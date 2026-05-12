<?php
namespace Tests;

use App\Apis\FinanceServicesManager;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\APITestCase;

class AnalyticsServiceTest extends APITestCase {
    public function testAccountBalances() {
        $this->login();
        $output = $this->getRequest($this->mgr(), 'analytics', ['report' => 'accountBalances']);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testAnalyticsRequiresAuth() {
        SessionsManager::destroy();
        $output = $this->getRequest($this->mgr(), 'analytics', ['report' => 'summary']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testByCategory() {
        $this->login();
        $output = $this->getRequest($this->mgr(), 'analytics', ['report' => 'byCategory']);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testMonthlyTrend() {
        $this->login();
        $output = $this->getRequest($this->mgr(), 'analytics', ['report' => 'monthlyTrend']);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testSummary() {
        $this->login();
        $output = $this->getRequest($this->mgr(), 'analytics', ['report' => 'summary']);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    private function login(): void {
        SessionsManager::start('wf-session');
        SessionsManager::set('user-id', 1);
    }
    private function mgr(): FinanceServicesManager {
        return new FinanceServicesManager();
    }
}
