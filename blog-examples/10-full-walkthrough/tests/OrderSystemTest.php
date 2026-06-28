<?php
namespace Tests;

use App\Domain\Order;
use App\Domain\OrderItem;
use App\Domain\Payment;
use App\Domain\Product;
use App\Domain\User;
use App\Events\OrderPlacedEvent;
use App\Events\PaymentCompletedEvent;
use App\Health\DatabaseCheck;
use App\Health\QueueCheck;
use App\Jobs\ProcessPaymentJob;
use App\Jobs\SendOrderConfirmationJob;
use App\Listeners\DecrementStockListener;
use App\Listeners\QueuePaymentListener;
use App\Listeners\SendConfirmationListener;
use App\Policies\OrderCancelPolicy;
use App\Policies\OrderViewPolicy;
use App\Services\MockPaymentGateway;
use App\Services\PaymentGatewayInterface;
use PHPUnit\Framework\TestCase;
use WebFiori\Container\ContainerFacade;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Framework\Health\HealthCheck;
use WebFiori\Queue\Job;
use WebFiori\Queue\QueueFacade;

class OrderSystemTest extends TestCase {
    private string $tmpQueueDir;

    protected function setUp(): void {
        parent::setUp();
        HealthCheck::reset();
        EventDispatcherFacade::reset();
        ContainerFacade::reset();

        // Use a fresh temp queue dir for each test
        $this->tmpQueueDir = sys_get_temp_dir() . '/wf-test-queue-' . uniqid();
        mkdir($this->tmpQueueDir, 0755, true);
        QueueFacade::setInstance(
            new \WebFiori\Queue\Queue(new \WebFiori\Queue\FileQueueStorage($this->tmpQueueDir))
        );
    }

    protected function tearDown(): void {
        HealthCheck::reset();
        EventDispatcherFacade::reset();
        ContainerFacade::reset();
        // Clean up temp queue dir
        array_map('unlink', glob($this->tmpQueueDir . '/*/*.json'));
        @rmdir($this->tmpQueueDir . '/pending');
        @rmdir($this->tmpQueueDir . '/processing');
        @rmdir($this->tmpQueueDir . '/completed');
        @rmdir($this->tmpQueueDir . '/failed');
        @rmdir($this->tmpQueueDir);
        QueueFacade::reset();
        parent::tearDown();
    }

    // ========== Domain Model Tests ==========

    public function testOrderDefaults() {
        $order = new Order();
        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        $this->assertEquals(0.0, $order->total);
        $this->assertNull($order->id);
    }

    public function testOrderStatuses() {
        $this->assertContains('pending', Order::VALID_STATUSES);
        $this->assertContains('paid', Order::VALID_STATUSES);
        $this->assertContains('shipped', Order::VALID_STATUSES);
        $this->assertContains('cancelled', Order::VALID_STATUSES);
    }

    public function testOrderItemCalculation() {
        $item = new OrderItem(productId: 1, quantity: 3, unitPrice: 25.50);
        $this->assertEquals(76.50, $item->quantity * $item->unitPrice);
    }

    public function testPaymentStatuses() {
        $payment = new Payment(orderId: 1, amount: 100, status: Payment::STATUS_COMPLETED, transactionId: 'txn_123');
        $this->assertEquals('completed', $payment->status);
        $this->assertEquals('txn_123', $payment->transactionId);
    }

    // ========== User / SecurityPrincipal Tests ==========

    public function testUserImplementsSecurityPrincipal() {
        $user = new User(id: 1, name: 'Alice', role: 'customer');
        $this->assertEquals(1, $user->getId());
        $this->assertEquals('Alice', $user->getName());
        $this->assertEquals(['customer'], $user->getRoles());
        $this->assertTrue($user->isActive());
    }

    public function testCustomerAuthorities() {
        $user = new User(role: 'customer');
        $authorities = $user->getAuthorities();
        $this->assertContains('orders.create', $authorities);
        $this->assertContains('orders.view', $authorities);
        $this->assertContains('orders.cancel', $authorities);
        $this->assertNotContains('orders.ship', $authorities);
    }

    public function testAdminAuthorities() {
        $user = new User(role: 'admin');
        $authorities = $user->getAuthorities();
        $this->assertContains('orders.manage', $authorities);
        $this->assertContains('products.manage', $authorities);
        $this->assertContains('orders.ship', $authorities);
    }

    public function testStaffAuthorities() {
        $user = new User(role: 'staff');
        $authorities = $user->getAuthorities();
        $this->assertContains('orders.view', $authorities);
        $this->assertContains('orders.ship', $authorities);
        $this->assertNotContains('orders.create', $authorities);
    }

    // ========== Policy Tests (ABAC) ==========

    public function testOrderViewPolicyAllowsOwner() {
        $user = new User(id: 5, role: 'customer');
        $order = new Order(userId: 5, status: 'pending');
        $this->assertTrue((new OrderViewPolicy())->evaluate($user, $order));
    }

    public function testOrderViewPolicyDeniesNonOwner() {
        $user = new User(id: 5, role: 'customer');
        $order = new Order(userId: 99, status: 'pending');
        $this->assertFalse((new OrderViewPolicy())->evaluate($user, $order));
    }

    public function testOrderViewPolicyAllowsAdmin() {
        $user = new User(id: 1, role: 'admin');
        $order = new Order(userId: 99, status: 'pending');
        $this->assertTrue((new OrderViewPolicy())->evaluate($user, $order));
    }

    public function testOrderViewPolicyAllowsStaff() {
        $user = new User(id: 2, role: 'staff');
        $order = new Order(userId: 99, status: 'pending');
        $this->assertTrue((new OrderViewPolicy())->evaluate($user, $order));
    }

    public function testOrderCancelPolicyAllowsOwnPending() {
        $user = new User(id: 5, role: 'customer');
        $order = new Order(userId: 5, status: 'pending');
        $this->assertTrue((new OrderCancelPolicy())->evaluate($user, $order));
    }

    public function testOrderCancelPolicyDeniesNonPending() {
        $user = new User(id: 5, role: 'customer');
        $order = new Order(userId: 5, status: 'paid');
        $this->assertFalse((new OrderCancelPolicy())->evaluate($user, $order));
    }

    public function testOrderCancelPolicyDeniesNonOwner() {
        $user = new User(id: 5, role: 'customer');
        $order = new Order(userId: 99, status: 'pending');
        $this->assertFalse((new OrderCancelPolicy())->evaluate($user, $order));
    }

    public function testOrderCancelPolicyAllowsAdmin() {
        $user = new User(id: 1, role: 'admin');
        $order = new Order(userId: 99, status: 'pending');
        $this->assertTrue((new OrderCancelPolicy())->evaluate($user, $order));
    }

    // ========== Payment Gateway Tests ==========

    public function testMockPaymentGatewaySuccess() {
        $gateway = new MockPaymentGateway();
        $result = $gateway->charge(100.00);
        $this->assertTrue($result['success']);
        $this->assertNotNull($result['transactionId']);
        $this->assertNull($result['error']);
    }

    public function testMockPaymentGatewayFailsOverLimit() {
        $gateway = new MockPaymentGateway();
        $result = $gateway->charge(10000.00);
        $this->assertFalse($result['success']);
        $this->assertNull($result['transactionId']);
        $this->assertEquals('Amount exceeds limit', $result['error']);
    }

    public function testMockPaymentGatewayBoundarySuccess() {
        $gateway = new MockPaymentGateway();
        $result = $gateway->charge(9999.00);
        $this->assertTrue($result['success']);
    }

    // ========== DI Container Tests ==========

    public function testContainerBindsInterface() {
        ContainerFacade::bind(PaymentGatewayInterface::class, MockPaymentGateway::class);
        $instance = ContainerFacade::make(PaymentGatewayInterface::class);
        $this->assertInstanceOf(MockPaymentGateway::class, $instance);
        $this->assertInstanceOf(PaymentGatewayInterface::class, $instance);
    }

    public function testContainerInstanceReturnsExact() {
        $specific = new MockPaymentGateway();
        ContainerFacade::instance(PaymentGatewayInterface::class, $specific);
        $resolved = ContainerFacade::make(PaymentGatewayInterface::class);
        $this->assertSame($specific, $resolved);
    }

    public function testContainerHas() {
        $this->assertFalse(ContainerFacade::has(PaymentGatewayInterface::class));
        ContainerFacade::bind(PaymentGatewayInterface::class, MockPaymentGateway::class);
        $this->assertTrue(ContainerFacade::has(PaymentGatewayInterface::class));
    }

    // ========== Event Dispatcher Tests ==========

    public function testEventDispatcherCallsListener() {
        $called = false;
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function (OrderPlacedEvent $event) use (&$called) {
            $called = true;
        });

        $order = new Order(id: 1, userId: 3, total: 50.0);
        EventDispatcherFacade::dispatch(new OrderPlacedEvent($order, []));
        $this->assertTrue($called);
    }

    public function testEventDispatcherMultipleListeners() {
        $count = 0;
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function () use (&$count) { $count++; });
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function () use (&$count) { $count++; });

        $order = new Order(id: 1, userId: 3, total: 50.0);
        EventDispatcherFacade::dispatch(new OrderPlacedEvent($order, []));
        $this->assertEquals(2, $count);
    }

    public function testQueuePaymentListenerDispatchesJob() {
        $order = new Order(id: 42, userId: 3, total: 99.99);
        $items = [new OrderItem(productId: 1, quantity: 2, unitPrice: 49.99)];
        $event = new OrderPlacedEvent($order, $items);

        $listener = new QueuePaymentListener();
        $listener->handle($event);

        $this->assertEquals(1, QueueFacade::getPendingCount());
    }

    public function testSendConfirmationListenerDispatchesJob() {
        $order = new Order(id: 10, userId: 1, total: 100.0);
        $payment = new Payment(orderId: 10, amount: 100.0, status: 'completed', transactionId: 'txn_abc');
        $event = new PaymentCompletedEvent($order, $payment);

        $listener = new SendConfirmationListener();
        $listener->handle($event);

        $this->assertEquals(1, QueueFacade::getPendingCount());
    }

    // ========== Job Interface Tests ==========

    public function testProcessPaymentJobImplementsJob() {
        $job = new ProcessPaymentJob(1, 100.0);
        $this->assertInstanceOf(Job::class, $job);
        $this->assertEquals(3, $job->getMaxAttempts());
        $this->assertEquals(30, $job->getRetryDelaySeconds());
    }

    public function testSendOrderConfirmationJobImplementsJob() {
        $job = new SendOrderConfirmationJob(1);
        $this->assertInstanceOf(Job::class, $job);
        $this->assertEquals(2, $job->getMaxAttempts());
        $this->assertEquals(60, $job->getRetryDelaySeconds());
    }

    // ========== Queue Tests ==========

    public function testQueueDispatchAndCount() {
        $job = new ProcessPaymentJob(1, 50.0);
        QueueFacade::dispatch($job, priority: 10);
        $this->assertEquals(1, QueueFacade::getPendingCount());
    }

    public function testQueueDispatchMultiple() {
        QueueFacade::dispatch(new ProcessPaymentJob(1, 50.0));
        QueueFacade::dispatch(new ProcessPaymentJob(2, 75.0));
        QueueFacade::dispatch(new SendOrderConfirmationJob(1));
        $this->assertEquals(3, QueueFacade::getPendingCount());
    }

    public function testQueueDispatchWithPriority() {
        QueueFacade::dispatch(new ProcessPaymentJob(1, 50.0), priority: 10);
        QueueFacade::dispatch(new SendOrderConfirmationJob(1), priority: 5);
        $this->assertEquals(2, QueueFacade::getPendingCount());
    }

    // ========== Health Check Tests ==========

    public function testQueueHealthCheck() {
        $check = new QueueCheck();
        $result = $check->check();
        $this->assertEquals('ok', $result->getStatus());
        $this->assertArrayHasKey('pending_jobs', $result->getMeta());
    }

    public function testHealthCheckAutoDiscoveryRegisters() {
        // Simulates what the framework does — register checks from App/Health/
        HealthCheck::register(new QueueCheck());
        HealthCheck::register(new DatabaseCheck());
        $this->assertEquals(2, HealthCheck::getCheckCount());
    }

    public function testHealthCheckRunAllAggregates() {
        HealthCheck::register(new QueueCheck());
        $result = HealthCheck::runAll();
        $this->assertEquals('ok', $result['status']);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertArrayHasKey('queue', $result['checks']);
    }

    public function testHealthCheckAfterAllCallback() {
        $captured = null;
        HealthCheck::register(new QueueCheck());
        HealthCheck::afterAll(function (array $result) use (&$captured) {
            $captured = $result;
        });
        HealthCheck::runAll();
        $this->assertNotNull($captured);
        $this->assertEquals('ok', $captured['status']);
    }

    // ========== Middleware Tests ==========

    public function testSecurityContextLoaderHasDependencies() {
        $middleware = new \App\Middleware\SecurityContextLoader();
        $this->assertEquals(['start-session'], $middleware->getDependencies());
    }

    public function testSecurityContextLoaderPriority() {
        $middleware = new \App\Middleware\SecurityContextLoader();
        $this->assertEquals(35000, $middleware->getPriority());
    }

    // ========== Integration: Event → Listener → Queue ==========

    public function testFullEventToQueueFlow() {
        // Register listeners like the app does
        EventDispatcherFacade::listen(OrderPlacedEvent::class, new QueuePaymentListener());

        // Place an order
        $order = new Order(id: 99, userId: 3, total: 250.0);
        $items = [new OrderItem(productId: 5, quantity: 1, unitPrice: 250.0)];

        EventDispatcherFacade::dispatch(new OrderPlacedEvent($order, $items));

        // Payment job should be queued
        $this->assertEquals(1, QueueFacade::getPendingCount());
    }

    public function testFullPaymentCompletedFlow() {
        EventDispatcherFacade::listen(PaymentCompletedEvent::class, new SendConfirmationListener());

        $order = new Order(id: 10, userId: 1, total: 100.0, status: 'paid');
        $payment = new Payment(orderId: 10, amount: 100.0, status: 'completed', transactionId: 'txn_xyz');

        EventDispatcherFacade::dispatch(new PaymentCompletedEvent($order, $payment));

        // Confirmation email job should be queued
        $this->assertEquals(1, QueueFacade::getPendingCount());
    }
}
