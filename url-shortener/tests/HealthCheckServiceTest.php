<?php
namespace Tests;

use App\Apis\LinkServicesManager;
use WebFiori\Http\APITestCase;

class HealthCheckServiceTest extends APITestCase {
    public function testHealthCheck() {
        $output = $this->getRequest(new LinkServicesManager(), 'health');
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }
}
