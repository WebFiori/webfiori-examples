<?php
namespace Tests;

use App\Apis\OrderServicesManager;
use App\Domain\Order;
use App\Events\OrderPlacedEvent;
use App\Health\DatabaseCheck;
use App\Health\QueueCheck;
use App\Policies\OrderCancelPolicy;
use App\Policies\OrderViewPolicy;
use App\Domain\User;
use App\Jobs\ProcessPaymentJob;
use App\Services\MockPaymentGateway;
use App\Services\PaymentGatewayInterface;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\APITestCase;
use WebFiori\Http\SecurityContext;
use WebFiori\Queue\QueueFacade;

class OrderSystemTest extends APITestCase {
    protected function setUp(): void {
        parent::setUp();
        QueueFacade::flush();
    }

    protected function tearDown(): void {
        SecurityContext::clear();
        parent::tearDown();
    }

    // --- Auth Tests ---

    public function testLoginSuccess() {
        $output = $this->postRequest($this->mgr(), 'auth', [
            'email' => 'admin@example.com',
            'password' => 'admin123',
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('admin', $response['data']['role']);
    }

    public function testLoginInvalidCredentials() {
        $output = $this->postRequest($this->mgr(), 'auth', [
            'email' => 'admin@example.com',
            'password' => 'wrong',
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    // --- Product Tests ---

    public function testListProducts() {
        $output = $this->getRequest($this->mgr(), 'products');
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);
    }

    public function testCreateProductRequiresAdmin() {
        $this->loginAs('customer');
        $output = $this->postRequest($this->mgr(), 'products', [
            'name' => 'Test', 'price' => 10.0,
        ], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testCreateProductAsAdmin() {
        $this->loginAs('admin');
        $output = $this->postRequest($this->mgr(), 'products', [
            'name' => 'New Product', 'price' => 25.50, 'stock' => 10,
        ], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    // --- Order Tests ---

    public function testCreateOrderAsCustomer() {
        $this->loginAs('customer');
        $items = json_encode([['productId' => 1, 'quantity' => 2]]);
        $output = $this->postRequest($this->mgr(), 'orders', ['items' => $items], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('order', $response['data']);
    }

    public function testCreateOrderQueuesPayment() {
        $this->loginAs('customer');
        $items = json_encode([['productId' => 1, 'quantity' => 1]]);
        $this->postRequest($this->mgr(), 'orders', ['items' => $items], [], $this->currentUser);
        $this->assertGreaterThan(0, QueueFacade::getPendingCount());
    }

    public function testListOrdersAsCustomer() {
        $this->loginAs('customer');
        $output = $this->getRequest($this->mgr(), 'orders', [], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testCancelOwnOrder() {
        $this->loginAs('customer');
        // Create an order first
        $items = json_encode([['productId' => 2, 'quantity' => 1]]);
        $this->postRequest($this->mgr(), 'orders', ['items' => $items], [], $this->currentUser);

        // Get orders to find the one we just created
        $output = $this->getRequest($this->mgr(), 'orders', [], [], $this->currentUser);
        $response = json_decode($output, true);
        $orders = $response['data'];
        $lastOrder = end($orders);

        $output = $this->deleteRequest($this->mgr(), 'orders', ['id' => $lastOrder['id']], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('cancelled', $response['data'][0]['status']);
    }

    public function testOrderRequiresAuth() {
        SecurityContext::clear();
        SessionsManager::destroy();
        $output = $this->getRequest($this->mgr(), 'orders');
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    // --- Policy Tests ---

    public function testOrderViewPolicyAllowsOwner() {
        $user = new User(id: 5, role: 'customer');
        $order = new Order(userId: 5, status: 'pending');
        $policy = new OrderViewPolicy();
        $this->assertTrue($policy->evaluate($user, $order));
    }

    public function testOrderViewPolicyDeniesNonOwner() {
        $user = new User(id: 5, role: 'customer');
        $order = new Order(userId: 99, status: 'pending');
        $policy = new OrderViewPolicy();
        $this->assertFalse($policy->evaluate($user, $order));
    }

    public function testOrderCancelPolicyDeniesNonPending() {
        $user = new User(id: 5, role: 'customer');
        $order = new Order(userId: 5, status: 'paid');
        $policy = new OrderCancelPolicy();
        $this->assertFalse($policy->evaluate($user, $order));
    }

    // --- Health Check Tests ---

    public function testDatabaseHealthCheck() {
        $check = new DatabaseCheck();
        $result = $check->check();
        $this->assertEquals('ok', $result->getStatus());
    }

    public function testQueueHealthCheck() {
        $check = new QueueCheck();
        $result = $check->check();
        $this->assertEquals('ok', $result->getStatus());
    }

    public function testHealthEndpoint() {
        $output = $this->getRequest($this->mgr(), 'health');
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('status', $response['data']);
    }

    // --- Job Tests ---

    public function testMockPaymentGateway() {
        $gateway = new MockPaymentGateway();
        $result = $gateway->charge(100.00);
        $this->assertTrue($result['success']);
        $this->assertNotNull($result['transactionId']);
    }

    public function testPaymentGatewayFailsOverLimit() {
        $gateway = new MockPaymentGateway();
        $result = $gateway->charge(10000.00);
        $this->assertFalse($result['success']);
    }

    // --- Helpers ---

    private function loginAs(string $role): void {
        $ids = ['admin' => 1, 'staff' => 2, 'customer' => 3];
        $names = ['admin' => 'Admin User', 'staff' => 'Staff Member', 'customer' => 'John Customer'];

        $this->currentUser = new \App\Domain\User(
            id: $ids[$role],
            name: $names[$role],
            role: $role
        );

        \WebFiori\Framework\Access::assignRoleToUser($ids[$role], $role);
    }

    private ?\App\Domain\User $currentUser = null;

    private function mgr(): OrderServicesManager {
        return new OrderServicesManager();
    }
}
