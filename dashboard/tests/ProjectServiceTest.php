<?php
namespace Tests;

use App\Apis\DashboardServicesManager;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\APITestCase;

class ProjectServiceTest extends APITestCase {
    private function mgr(): DashboardServicesManager {
        return new DashboardServicesManager();
    }

    public function testListProjectsRequiresAuth() {
        SessionsManager::destroy();
        $output = $this->getRequest($this->mgr(), 'projects');
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testCreateProjectRequiresAuth() {
        SessionsManager::destroy();
        $output = $this->postRequest($this->mgr(), 'projects', ['name' => 'Test']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testCreateProjectMissingName() {
        $output = $this->postRequest($this->mgr(), 'projects', []);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('name', $response['message']);
    }

    public function testDeleteProjectMissingId() {
        $output = $this->deleteRequest($this->mgr(), 'projects', []);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('id', $response['message']);
    }
}
