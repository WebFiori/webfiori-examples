<?php
namespace Tests;

use App\Apis\FinanceServicesManager;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\APITestCase;

class TransactionServiceTest extends APITestCase {
    public function testCreateTransaction() {
        $this->login();
        $output = $this->postRequest($this->mgr(), 'transactions', [
            'accountId' => 1, 'type' => 'expense', 'amount' => '50', 'date' => date('Y-m-d'), 'description' => 'Test',
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testCreateTransactionMissingAmount() {
        $this->login();
        $output = $this->postRequest($this->mgr(), 'transactions', ['accountId' => 1, 'type' => 'expense', 'date' => date('Y-m-d')]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('amount', $response['message']);
    }

    public function testDeleteTransactionNotFound() {
        $this->login();
        $output = $this->deleteRequest($this->mgr(), 'transactions', ['id' => 99999]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testFilterByType() {
        $this->login();
        $output = $this->getRequest($this->mgr(), 'transactions', ['type' => 'income']);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testListTransactions() {
        $this->login();
        $output = $this->getRequest($this->mgr(), 'transactions');
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testListTransactionsRequiresAuth() {
        SessionsManager::destroy();
        $output = $this->getRequest($this->mgr(), 'transactions');
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
