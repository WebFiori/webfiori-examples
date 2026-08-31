<?php
namespace Tests;

use App\Apis\OrderService;
use App\Database\Migrations\CreateOrderTables;
use App\Database\Seeders\SeedSampleData;
use App\Domain\Order;
use App\Domain\User;
use App\Events\OrderPlacedEvent;
use App\Infrastructure\Repository\OrderRepository;
use App\Infrastructure\Repository\ProductRepository;
use App\Listeners\DecrementStockListener;
use App\Listeners\QueuePaymentListener;
use App\Policies\OrderCancelPolicy;
use App\Policies\OrderViewPolicy;
use App\Services\MockPaymentGateway;
use App\Services\PaymentGatewayInterface;
use PHPUnit\Framework\TestCase;
use WebFiori\Container\ContainerFacade;
use WebFiori\Database\Database;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Framework\Access;
use WebFiori\Framework\App;
use WebFiori\Http\Exceptions\BadRequestException;
use WebFiori\Http\Exceptions\ForbiddenException;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\SecurityContext;
use WebFiori\Queue\FileQueueStorage;
use WebFiori\Queue\Queue;
use WebFiori\Queue\QueueFacade;

/**
 * Exercises the real OrderService endpoint logic (createOrder / getOrders /
 * cancelOrder / shipOrder), including validation error paths and ABAC checks.
 *
 * The service builds its own Database from the app config's "orders" connection.
 * That connection points at an on-disk SQLite file (orders.db), which we create,
 * migrate and seed here, then remove afterwards. An on-disk file is required
 * because the service opens several independent Database instances per request.
 */
class OrderServiceTest extends TestCase {
    private static string $dbFile;
    private string $tmpQueueDir;

    public static function setUpBeforeClass(): void {
        $conn = App::getConfig()->getDBConnection('orders');
        self::$dbFile = $conn->getDBName();

        // Start from a clean database file.
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

        // RBAC roles + ABAC policies, mirroring App/Ini/Privileges.php.
        Access::role('customer', ['orders.create', 'orders.view', 'orders.cancel']);
        Access::role('staff', ['orders.view', 'orders.update', 'orders.ship']);
        Access::role('admin', ['orders.create', 'orders.view', 'orders.cancel', 'orders.update', 'orders.ship', 'orders.manage', 'products.manage']);
        Access::registerPolicy(new OrderViewPolicy());
        Access::registerPolicy(new OrderCancelPolicy());

        ContainerFacade::bind(PaymentGatewayInterface::class, MockPaymentGateway::class);

        // Fresh isolated queue per test.
        $this->tmpQueueDir = sys_get_temp_dir().'/wf-os-queue-'.uniqid();
        mkdir($this->tmpQueueDir, 0755, true);
        QueueFacade::setInstance(new Queue(new FileQueueStorage($this->tmpQueueDir)));
    }

    protected function tearDown(): void {
        SecurityContext::clear();
        EventDispatcherFacade::reset();
        ContainerFacade::reset();
        array_map('unlink', glob($this->tmpQueueDir.'/*/*.json'));
        @rmdir($this->tmpQueueDir.'/pending');
        @rmdir($this->tmpQueueDir.'/failed');
        @rmdir($this->tmpQueueDir);
        QueueFacade::reset();
        parent::tearDown();
    }

    private function loginAs(int $id, string $role): User {
        $user = new User(id: $id, name: 'Test '.$role, role: $role);
        SecurityContext::setCurrentUser($user);
        Access::assignRoleToUser($user->getId(), $role);

        return $user;
    }

    private function db(): Database {
        return new Database(App::getConfig()->getDBConnection('orders'));
    }

    // ========== createOrder: success ==========

    public function testCreateOrderPersistsOrderAndItems() {
        $this->loginAs(3, 'customer');

        $items = json_encode([['productId' => 1, 'quantity' => 2]]);
        $result = (new OrderService())->createOrder($items);

        $this->assertArrayHasKey('order', $result);
        $this->assertArrayHasKey('items', $result);

        $order = $result['order'];
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(3, $order->userId);
        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        // Product 1 (Wireless Keyboard) is 79.99 x 2 = 159.98
        $this->assertEqualsWithDelta(159.98, $order->total, 0.001);
        $this->assertCount(1, $result['items']);
        $this->assertEquals(2, $result['items'][0]->quantity);
        $this->assertNotNull($order->id, 'Saved order should have an id');
    }

    public function testCreateOrderComputesTotalAcrossMultipleItems() {
        $this->loginAs(3, 'customer');

        // Product 1 = 79.99 x 1, Product 2 (USB-C Hub) = 49.99 x 2 => 179.97
        $items = json_encode([
            ['productId' => 1, 'quantity' => 1],
            ['productId' => 2, 'quantity' => 2],
        ]);
        $result = (new OrderService())->createOrder($items);

        $this->assertEqualsWithDelta(179.97, $result['order']->total, 0.001);
        $this->assertCount(2, $result['items']);
    }

    public function testCreateOrderDispatchesOrderPlacedEvent() {
        $this->loginAs(3, 'customer');

        $captured = null;
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function (OrderPlacedEvent $e) use (&$captured) {
            $captured = $e;
        });

        (new OrderService())->createOrder(json_encode([['productId' => 1, 'quantity' => 1]]));

        $this->assertInstanceOf(OrderPlacedEvent::class, $captured);
        $this->assertEqualsWithDelta(79.99, $captured->order->total, 0.001);
        $this->assertCount(1, $captured->items);
    }

    // ========== createOrder: error paths ==========

    public function testCreateOrderRejectsEmptyItems() {
        $this->loginAs(3, 'customer');
        $this->expectException(BadRequestException::class);
        (new OrderService())->createOrder(json_encode([]));
    }

    public function testCreateOrderRejectsInvalidJson() {
        $this->loginAs(3, 'customer');
        $this->expectException(BadRequestException::class);
        (new OrderService())->createOrder('not-json');
    }

    public function testCreateOrderRejectsItemMissingFields() {
        $this->loginAs(3, 'customer');
        $this->expectException(BadRequestException::class);
        (new OrderService())->createOrder(json_encode([['productId' => 1]]));
    }

    public function testCreateOrderRejectsUnknownProduct() {
        $this->loginAs(3, 'customer');
        $this->expectException(NotFoundException::class);
        (new OrderService())->createOrder(json_encode([['productId' => 9999, 'quantity' => 1]]));
    }

    public function testCreateOrderRejectsInsufficientStock() {
        $this->loginAs(3, 'customer');
        $this->expectException(BadRequestException::class);
        // Desk Lamp has 200 in stock; request far more.
        (new OrderService())->createOrder(json_encode([['productId' => 5, 'quantity' => 999999]]));
    }

    // ========== getOrders ==========

    public function testGetOrdersByIdReturnsOwnOrder() {
        $user = $this->loginAs(3, 'customer');
        $repo = new OrderRepository($this->db());
        $order = new Order(userId: $user->getId(), status: Order::STATUS_PENDING, total: 10.0, createdAt: date('Y-m-d H:i:s'));
        $repo->save($order);
        $saved = end($repo->findByUserId($user->getId()));

        $result = (new OrderService())->getOrders($saved->id);

        $this->assertArrayHasKey('order', $result);
        $this->assertEquals($saved->id, $result['order']->id);
    }

    public function testGetOrdersByIdNotFound() {
        $this->loginAs(3, 'customer');
        $this->expectException(NotFoundException::class);
        (new OrderService())->getOrders(987654);
    }

    public function testGetOrdersDeniesViewingOthersOrder() {
        // Order owned by user 4, requested by user 3 (customer) -> ABAC denial.
        $repo = new OrderRepository($this->db());
        $order = new Order(userId: 4, status: Order::STATUS_PENDING, total: 20.0, createdAt: date('Y-m-d H:i:s'));
        $repo->save($order);
        $saved = end($repo->findByUserId(4));

        $this->loginAs(3, 'customer');
        $this->expectException(ForbiddenException::class);
        (new OrderService())->getOrders($saved->id);
    }

    // ========== cancelOrder (ABAC via Access::can) ==========

    public function testCancelOrderAllowsOwnerPending() {
        $user = $this->loginAs(3, 'customer');
        $repo = new OrderRepository($this->db());
        $order = new Order(userId: $user->getId(), status: Order::STATUS_PENDING, total: 30.0, createdAt: date('Y-m-d H:i:s'));
        $repo->save($order);
        $saved = end($repo->findByUserId($user->getId()));

        $result = (new OrderService())->cancelOrder($saved->id);

        $this->assertEquals(Order::STATUS_CANCELLED, $result[0]->status);
    }

    public function testCancelOrderDeniesNonOwner() {
        $repo = new OrderRepository($this->db());
        $order = new Order(userId: 4, status: Order::STATUS_PENDING, total: 30.0, createdAt: date('Y-m-d H:i:s'));
        $repo->save($order);
        $saved = end($repo->findByUserId(4));

        $this->loginAs(3, 'customer');
        $this->expectException(ForbiddenException::class);
        (new OrderService())->cancelOrder($saved->id);
    }

    public function testCancelOrderNotFound() {
        $this->loginAs(3, 'customer');
        $this->expectException(NotFoundException::class);
        (new OrderService())->cancelOrder(555111);
    }

    // ========== shipOrder ==========

    public function testShipOrderRejectsUnpaidOrder() {
        $user = $this->loginAs(2, 'staff');
        $repo = new OrderRepository($this->db());
        $order = new Order(userId: 3, status: Order::STATUS_PENDING, total: 40.0, createdAt: date('Y-m-d H:i:s'));
        $repo->save($order);
        $saved = end($repo->findByUserId(3));

        $this->expectException(BadRequestException::class);
        (new OrderService())->shipOrder($saved->id);
    }

    public function testShipOrderShipsPaidOrder() {
        $repo = new OrderRepository($this->db());
        $order = new Order(userId: 3, status: Order::STATUS_PAID, total: 40.0, createdAt: date('Y-m-d H:i:s'));
        $repo->save($order);
        $saved = end($repo->findByUserId(3));

        $this->loginAs(2, 'staff');
        $result = (new OrderService())->shipOrder($saved->id);

        $this->assertEquals(Order::STATUS_SHIPPED, $result[0]->status);
    }

    // ========== ABAC through the Access facade (what the blog advertises) ==========

    public function testAccessCanEnforcesOwnershipForCancel() {
        $owner = new User(id: 3, role: 'customer');
        $other = new User(id: 7, role: 'customer');
        $admin = new User(id: 1, role: 'admin');
        $pending = new Order(userId: 3, status: Order::STATUS_PENDING);
        $paid = new Order(userId: 3, status: Order::STATUS_PAID);

        $this->assertTrue(Access::can($owner, 'orders.cancel', $pending));
        $this->assertFalse(Access::can($other, 'orders.cancel', $pending));
        $this->assertFalse(Access::can($owner, 'orders.cancel', $paid), 'Paid orders cannot be cancelled');
        $this->assertTrue(Access::can($admin, 'orders.cancel', $pending), 'Admin overrides ownership');
    }
}
