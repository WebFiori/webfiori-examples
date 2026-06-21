<?php
namespace Tests;

use App\Apis\BillingServicesManager;
use App\Domain\Invoice;
use App\Domain\User;
use App\Health\BillingProviderCheck;
use App\Health\DatabaseCheck;
use App\Policies\InvoiceViewPolicy;
use App\Services\MockBillingProvider;
use WebFiori\Framework\Access;
use WebFiori\Http\APITestCase;
use WebFiori\Http\SecurityContext;
use WebFiori\Queue\QueueFacade;

class BillingSystemTest extends APITestCase {
    private ?User $currentUser = null;

    protected function setUp(): void {
        parent::setUp();
        QueueFacade::flush();
    }

    protected function tearDown(): void {
        SecurityContext::clear();
        parent::tearDown();
    }

    // --- Auth ---

    public function testLoginSuccess() {
        $output = $this->postRequest($this->mgr(), 'auth', [
            'email' => 'super@platform.com', 'password' => 'super123',
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('super-admin', $response['data']['role']);
    }

    public function testLoginFails() {
        $output = $this->postRequest($this->mgr(), 'auth', [
            'email' => 'super@platform.com', 'password' => 'wrong',
        ]);
        $this->assertEquals('error', json_decode($output, true)['type']);
    }

    // --- Tenants ---

    public function testListTenantsAsSuperAdmin() {
        $this->loginAs('super-admin');
        $output = $this->getRequest($this->mgr(), 'tenants', [], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertGreaterThanOrEqual(2, count($response['data']));
    }

    public function testListTenantsDeniedForMember() {
        $this->loginAs('member');
        $output = $this->getRequest($this->mgr(), 'tenants', [], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    // --- Invoices ---

    public function testListInvoicesAsTenantAdmin() {
        $this->loginAs('tenant-admin');
        $output = $this->getRequest($this->mgr(), 'invoices', [], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testGenerateInvoiceAsSuperAdmin() {
        $this->loginAs('super-admin');
        $output = $this->postRequest($this->mgr(), 'invoices', [
            'tenantId' => 1, 'amount' => 149.00, 'period' => '2026-05',
        ], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('pending', $response['data'][0]['status']);
    }

    public function testGenerateInvoiceQueuesPayment() {
        $this->loginAs('super-admin');
        $this->postRequest($this->mgr(), 'invoices', [
            'tenantId' => 2, 'amount' => 49.00, 'period' => '2026-05',
        ], [], $this->currentUser);
        $this->assertGreaterThan(0, QueueFacade::getPendingCount());
    }

    public function testGenerateInvoiceDeniedForMember() {
        $this->loginAs('member');
        $output = $this->postRequest($this->mgr(), 'invoices', [
            'tenantId' => 1, 'amount' => 100.00,
        ], [], $this->currentUser);
        $this->assertEquals('error', json_decode($output, true)['type']);
    }

    public function testRequiresAuth() {
        SecurityContext::clear();
        $output = $this->getRequest($this->mgr(), 'invoices');
        $this->assertEquals('error', json_decode($output, true)['type']);
    }

    // --- Policies ---

    public function testInvoiceViewPolicyAllowsSameTenant() {
        $user = new User(id: 2, tenantId: 1, role: 'tenant-admin');
        $invoice = new Invoice(tenantId: 1);
        $this->assertTrue((new InvoiceViewPolicy())->evaluate($user, $invoice));
    }

    public function testInvoiceViewPolicyDeniesDifferentTenant() {
        $user = new User(id: 2, tenantId: 1, role: 'tenant-admin');
        $invoice = new Invoice(tenantId: 2);
        $this->assertFalse((new InvoiceViewPolicy())->evaluate($user, $invoice));
    }

    public function testInvoiceViewPolicyAllowsSuperAdmin() {
        $user = new User(id: 1, tenantId: 0, role: 'super-admin');
        $invoice = new Invoice(tenantId: 2);
        $this->assertTrue((new InvoiceViewPolicy())->evaluate($user, $invoice));
    }

    // --- Health ---

    public function testDatabaseHealth() {
        $this->assertEquals('ok', (new DatabaseCheck())->check()->getStatus());
    }

    public function testBillingProviderHealth() {
        $this->assertEquals('ok', (new BillingProviderCheck())->check()->getStatus());
    }

    public function testHealthEndpoint() {
        $output = $this->getRequest($this->mgr(), 'health');
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    // --- Billing Provider ---

    public function testMockBillingSuccess() {
        $result = (new MockBillingProvider())->charge(1, 100.00);
        $this->assertTrue($result['success']);
    }

    public function testMockBillingDecline() {
        $result = (new MockBillingProvider())->charge(1, 6000.00);
        $this->assertFalse($result['success']);
    }

    // --- Helpers ---

    private function loginAs(string $role): void {
        $map = [
            'super-admin' => [1, 0, 'Super Admin'],
            'tenant-admin' => [2, 1, 'Acme Admin'],
            'member' => [3, 1, 'Acme Member'],
        ];
        [$id, $tenantId, $name] = $map[$role];
        $this->currentUser = new User(id: $id, tenantId: $tenantId, name: $name, role: $role);
        Access::assignRoleToUser($id, $role);
    }

    private function mgr(): BillingServicesManager {
        return new BillingServicesManager();
    }
}
