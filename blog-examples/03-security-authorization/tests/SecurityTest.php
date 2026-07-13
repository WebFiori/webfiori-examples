<?php
namespace Tests;

use App\Domain\Order;
use App\Domain\User;
use App\Policies\OrderCancelPolicy;
use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Access;
use WebFiori\Http\SecurityContext;

class SecurityTest extends TestCase {

    protected function setUp(): void {
        SecurityContext::clear();
    }

    public function testSecurityContextSetUser(): void {
        $user = new User(1, 'Alice', 'admin');
        SecurityContext::setCurrentUser($user);

        $this->assertTrue(SecurityContext::isAuthenticated());
        $this->assertTrue(SecurityContext::hasRole('admin'));
        $this->assertTrue(SecurityContext::hasAuthority('orders.manage'));
        $this->assertFalse(SecurityContext::hasRole('customer'));
    }

    public function testSecurityContextClear(): void {
        SecurityContext::setCurrentUser(new User(1, 'Alice', 'admin'));
        SecurityContext::clear();

        $this->assertFalse(SecurityContext::isAuthenticated());
    }

    public function testExpressionEvaluation(): void {
        SecurityContext::setCurrentUser(new User(1, 'Alice', 'admin'));

        $this->assertTrue(SecurityContext::evaluateExpression("hasRole('admin')"));
        $this->assertFalse(SecurityContext::evaluateExpression("hasRole('customer')"));
        $this->assertTrue(SecurityContext::evaluateExpression("isAuthenticated()"));
        $this->assertTrue(SecurityContext::evaluateExpression("hasAuthority('orders.manage')"));
        $this->assertTrue(SecurityContext::evaluateExpression("hasRole('admin') || hasRole('customer')"));
        $this->assertFalse(SecurityContext::evaluateExpression("hasRole('admin') && hasRole('customer')"));
    }

    public function testRbacPermissionCheck(): void {
        $admin = new User(1, 'Alice', 'admin');
        $customer = new User(2, 'Bob', 'customer');

        $this->assertTrue(Access::can($admin, 'orders.manage'));
        $this->assertFalse(Access::can($customer, 'orders.manage'));
        $this->assertTrue(Access::can($customer, 'orders.create'));
    }

    public function testAbacPolicyCustomerOwnsOrder(): void {
        $customer = new User(2, 'Bob', 'customer');
        Access::assignRoleToUser(2, 'customer');

        $ownOrder = new Order(1, 2, 50.00, 'pending');
        $otherOrder = new Order(2, 99, 50.00, 'pending');

        $this->assertTrue(Access::can($customer, 'orders.cancel', $ownOrder));
        $this->assertFalse(Access::can($customer, 'orders.cancel', $otherOrder));
    }

    public function testAbacPolicyAdminCancelAny(): void {
        $admin = new User(1, 'Alice', 'admin');
        Access::assignRoleToUser(1, 'admin');

        $order = new Order(1, 99, 100.00, 'pending');
        $this->assertTrue(Access::can($admin, 'orders.cancel', $order));
    }

    public function testAbacPolicyCannotCancelShippedOrder(): void {
        $admin = new User(1, 'Alice', 'admin');
        Access::assignRoleToUser(1, 'admin');

        $shippedOrder = new Order(1, 1, 100.00, 'shipped');
        $this->assertFalse(Access::can($admin, 'orders.cancel', $shippedOrder));
    }
}
