<?php
namespace Tests;

use App\Domain\Order;
use App\Domain\OrderItem;
use App\Domain\Payment;
use App\Events\OrderPlacedEvent;
use App\Events\PaymentCompletedEvent;
use App\Infrastructure\Repository\OrderRepository;
use App\Infrastructure\Repository\PaymentRepository;
use App\Jobs\ProcessPaymentJob;
use App\Jobs\SendOrderConfirmationJob;
use App\Listeners\QueuePaymentListener;
use App\Services\MockPaymentGateway;
use App\Services\PaymentGatewayInterface;
use PHPUnit\Framework\TestCase;
use WebFiori\Container\ContainerFacade;
use WebFiori\Database\Database;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Framework\App;
use App\Database\Migrations\CreateOrderTables;
use App\Database\Seeders\SeedSampleData;
use WebFiori\Queue\FileQueueStorage;
use WebFiori\Queue\Queue;
use WebFiori\Queue\QueueFacade;

/**
 * Executes the real ProcessPaymentJob::handle() logic against the app's
 * "orders" SQLite connection, and asserts the contents of jobs that listeners
 * push onto the queue (job type + order id/amount), not just the pending count.
 */
class ProcessPaymentJobTest extends TestCase {
    private static string $dbFile;
    private string $tmpQueueDir;

    public static function setUpBeforeClass(): void {
        $conn = App::getConfig()->getDBConnection('orders');
        self::$dbFile = $conn->getDBName();

        if (file_exists(self::$dbFile)) {
            @unlink(self::$dbFile);
        }
        touch(self::$dbFile);

        $db = new Database($conn);
        (new CreateOrderTables())->up($db);
        (new SeedSampleData())->up($db);
    }

    public static function tearDownAfterClass(): void {
        if (isset(self::$dbFile) && file_exists(self::$dbFile)) {
            @unlink(self::$dbFile);
        }
    }

    protected function setUp(): void {
        parent::setUp();
        EventDispatcherFacade::reset();
        ContainerFacade::reset();
        ContainerFacade::bind(PaymentGatewayInterface::class, MockPaymentGateway::class);

        $this->tmpQueueDir = sys_get_temp_dir().'/wf-job-queue-'.uniqid();
        mkdir($this->tmpQueueDir, 0755, true);
        QueueFacade::setInstance(new Queue(new FileQueueStorage($this->tmpQueueDir)));
    }

    protected function tearDown(): void {
        EventDispatcherFacade::reset();
        ContainerFacade::reset();
        array_map('unlink', glob($this->tmpQueueDir.'/*/*.json'));
        @rmdir($this->tmpQueueDir.'/pending');
        @rmdir($this->tmpQueueDir.'/failed');
        @rmdir($this->tmpQueueDir);
        QueueFacade::reset();
        parent::tearDown();
    }

    private function db(): Database {
        return new Database(App::getConfig()->getDBConnection('orders'));
    }

    /** Persist a pending order with the given total and return its id. */
    private function seedPendingOrder(float $total, int $userId = 3): int {
        $repo = new OrderRepository($this->db());
        $repo->save(new Order(userId: $userId, status: Order::STATUS_PENDING, total: $total, createdAt: date('Y-m-d H:i:s')));

        return end($repo->findByUserId($userId))->id;
    }

    // ========== handle(): success path ==========

    public function testHandleSuccessMarksOrderPaid() {
        $orderId = $this->seedPendingOrder(120.00);

        (new ProcessPaymentJob($orderId, 120.00))->handle();

        $order = (new OrderRepository($this->db()))->findById($orderId);
        $this->assertEquals(Order::STATUS_PAID, $order->status);
    }

    public function testHandleSuccessRecordsCompletedPayment() {
        $orderId = $this->seedPendingOrder(75.50);

        (new ProcessPaymentJob($orderId, 75.50))->handle();

        $payments = (new PaymentRepository($this->db()))->findByOrderId($orderId);
        $this->assertCount(1, $payments);
        $this->assertEquals(Payment::STATUS_COMPLETED, $payments[0]->status);
        $this->assertNotNull($payments[0]->transactionId);
        $this->assertEqualsWithDelta(75.50, $payments[0]->amount, 0.001);
    }

    public function testHandleSuccessDispatchesPaymentCompletedEvent() {
        $orderId = $this->seedPendingOrder(60.00);

        $captured = null;
        EventDispatcherFacade::listen(PaymentCompletedEvent::class, function (PaymentCompletedEvent $e) use (&$captured) {
            $captured = $e;
        });

        (new ProcessPaymentJob($orderId, 60.00))->handle();

        $this->assertInstanceOf(PaymentCompletedEvent::class, $captured);
        $this->assertEquals($orderId, $captured->order->id);
        $this->assertEquals(Payment::STATUS_COMPLETED, $captured->payment->status);
    }

    // ========== handle(): failure path ==========

    public function testHandleFailureThrowsAndRecordsFailedPayment() {
        // MockPaymentGateway fails for amounts over 9999.
        $orderId = $this->seedPendingOrder(15000.00);

        try {
            (new ProcessPaymentJob($orderId, 15000.00))->handle();
            $this->fail('Expected RuntimeException on payment failure.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Payment failed', $e->getMessage());
        }

        // Order stays pending; a failed payment row is recorded.
        $order = (new OrderRepository($this->db()))->findById($orderId);
        $this->assertEquals(Order::STATUS_PENDING, $order->status);

        $payments = (new PaymentRepository($this->db()))->findByOrderId($orderId);
        $this->assertCount(1, $payments);
        $this->assertEquals(Payment::STATUS_FAILED, $payments[0]->status);
    }

    public function testHandleFailureDoesNotDispatchCompletedEvent() {
        $orderId = $this->seedPendingOrder(20000.00);

        $dispatched = false;
        EventDispatcherFacade::listen(PaymentCompletedEvent::class, function () use (&$dispatched) {
            $dispatched = true;
        });

        try {
            (new ProcessPaymentJob($orderId, 20000.00))->handle();
        } catch (\RuntimeException $e) {
            // expected
        }

        $this->assertFalse($dispatched, 'PaymentCompletedEvent must not fire on failure');
    }

    // ========== handle(): idempotency guard ==========

    public function testHandleSkipsNonPendingOrder() {
        // Seed an order that is already paid.
        $repo = new OrderRepository($this->db());
        $repo->save(new Order(userId: 3, status: Order::STATUS_PAID, total: 99.0, createdAt: date('Y-m-d H:i:s')));
        $orderId = end($repo->findByUserId(3))->id;

        (new ProcessPaymentJob($orderId, 99.0))->handle();

        // No payment recorded because the job short-circuits on non-pending orders.
        $payments = (new PaymentRepository($this->db()))->findByOrderId($orderId);
        $this->assertCount(0, $payments);
    }

    // ========== Queued-job payload assertions (type + contents) ==========

    public function testQueuePaymentListenerQueuesProcessPaymentJobWithCorrectData() {
        $order = new Order(id: 4242, userId: 3, total: 333.33);
        $items = [new OrderItem(productId: 1, quantity: 1, unitPrice: 333.33)];

        (new QueuePaymentListener())->handle(new OrderPlacedEvent($order, $items));

        $job = $this->firstPendingJob();
        $this->assertInstanceOf(ProcessPaymentJob::class, $job);
        $this->assertEquals(4242, $this->readPrivate($job, 'orderId'));
        $this->assertEqualsWithDelta(333.33, $this->readPrivate($job, 'amount'), 0.001);
    }

    public function testProcessedQueueRunsJobAndClearsPending() {
        $orderId = $this->seedPendingOrder(45.00);

        QueueFacade::dispatch(new ProcessPaymentJob($orderId, 45.00), priority: 10);
        $this->assertEquals(1, QueueFacade::getPendingCount());

        $processed = QueueFacade::process();

        $this->assertEquals(1, $processed);
        $this->assertEquals(0, QueueFacade::getPendingCount());
        $order = (new OrderRepository($this->db()))->findById($orderId);
        $this->assertEquals(Order::STATUS_PAID, $order->status);
    }

    // ========== helpers to read the opaque queued payload ==========

    /**
     * Reads the first pending job by deserializing its stored payload.
     * With no QUEUE_KEY set, payloads are plaintext serialize() strings.
     */
    private function firstPendingJob(): object {
        $files = glob($this->tmpQueueDir.'/pending/*.json');
        $this->assertNotEmpty($files, 'Expected a pending job file');
        $data = json_decode(file_get_contents($files[0]), true);
        $this->assertArrayHasKey('payload', $data);

        return unserialize($data['payload']);
    }

    private function readPrivate(object $obj, string $prop): mixed {
        $ref = new \ReflectionProperty($obj, $prop);
        $ref->setAccessible(true);

        return $ref->getValue($obj);
    }
}
