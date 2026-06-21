<?php
namespace Tests;

use App\Apis\FinanceServicesManager;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\APITestCase;

class AccountServiceTest extends APITestCase {
    public function testCreateAccount() {
        $this->login();
        $output = $this->postRequest($this->mgr(), 'accounts', ['name' => 'Test Account', 'type' => 'savings', 'balance' => '1000']);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testCreateAccountMissingName() {
        $this->login();
        $output = $this->postRequest($this->mgr(), 'accounts', []);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('name', $response['message']);
    }

    public function testDeleteAccountNotFound() {
        $this->login();
        $output = $this->deleteRequest($this->mgr(), 'accounts', ['id' => 99999]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testListAccounts() {
        $this->login();
        $output = $this->getRequest($this->mgr(), 'accounts');
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testListAccountsRequiresAuth() {
        SessionsManager::destroy();
        $output = $this->getRequest($this->mgr(), 'accounts');
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    private function login(): void {
        SessionsManager::start('wf-session');
        SessionsManager::set('user-id', 1);
    }
    private function mgr(): FinanceServicesManager {
        return new FinanceServicesManager();
    }
}
