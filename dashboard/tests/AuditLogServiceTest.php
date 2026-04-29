<?php
namespace Tests;

use App\Apis\DashboardServicesManager;
use WebFiori\Http\APITestCase;

class AuditLogServiceTest extends APITestCase {
    public function testAuditLogRequiresAdmin() {
        $output = $this->getRequest($this->mgr(), 'audit-log');
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }
    private function mgr(): DashboardServicesManager {
        return new DashboardServicesManager();
    }
}
