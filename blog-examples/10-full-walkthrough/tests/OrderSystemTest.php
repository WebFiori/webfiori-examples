<?php
namespace Tests;

use App\Domain\Order;
use App\Domain\User;
use App\Health\DatabaseCheck;
use App\Health\QueueCheck;
use App\Policies\OrderCancelPolicy;
use App\Policies\OrderViewPolicy;
use App\Services\MockPaymentGateway;
use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Health\HealthCheck;

class OrderSystemTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        HealthCheck::reset();
    }

    // --- Policy Tests (no DB required) ---

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

    public function testOrderViewPolicyAllowsAdmin() {
        $user = new User(id: 1, role: 'admin');
        $order = new Order(userId: 99, status: 'pending');
        $policy = new OrderViewPolicy();
        $this->assertTrue($policy->evaluate($user, $order));
    }

    public function testOrderCancelPolicyAllowsOwnPending() {
        $user = new User(id: 5, role: 'customer');
        $order = new Order(userId: 5, status: 'pending');
        $policy = new OrderCancelPolicy();
        $this->assertTrue($policy->evaluate($user, $order));
    }

    public function testOrderCancelPolicyDeniesNonPending() {
        $user = new User(id: 5, role: 'customer');
        $order = new Order(userId: 5, status: 'paid');
        $policy = new OrderCancelPolicy();
        $this->assertFalse($policy->evaluate($user, $order));
    }

    public function testOrderCancelPolicyDeniesNonOwner() {
        $user = new User(id: 5, role: 'customer');
        $order = new Order(userId: 99, status: 'pending');
        $policy = new OrderCancelPolicy();
        $this->assertFalse($policy->evaluate($user, $order));
    }

    // --- Payment Gateway Tests (no DB required) ---

    public function testMockPaymentGatewaySuccess() {
        $gateway = new MockPaymentGateway();
        $result = $gateway->charge(100.00);
        $this->assertTrue($result['success']);
        $this->assertNotNull($result['transactionId']);
    }

    public function testMockPaymentGatewayFailsOverLimit() {
        $gateway = new MockPaymentGateway();
        $result = $gateway->charge(10000.00);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    // --- Health Check Tests ---

    public function testQueueHealthCheck() {
        $check = new QueueCheck();
        $result = $check->check();
        $this->assertEquals('ok', $result->getStatus());
    }

    public function testHealthCheckRegistration() {
        HealthCheck::register(new QueueCheck());
        $result = HealthCheck::runAll();
        $this->assertArrayHasKey('queue', $result['checks']);
    }
}
