<?php
namespace Tests;

use App\Apis\DashboardServicesManager;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\APITestCase;

class ReportServiceTest extends APITestCase {
    private function mgr(): DashboardServicesManager {
        return new DashboardServicesManager();
    }

    public function testListReportsRequiresAuth() {
        SessionsManager::destroy();
        $output = $this->getRequest($this->mgr(), 'reports');
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testGenerateReportRequiresAuth() {
        SessionsManager::destroy();
        $output = $this->postRequest($this->mgr(), 'reports');
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }
}
