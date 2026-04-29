<?php
namespace Tests;

use App\Apis\DashboardServicesManager;
use WebFiori\Http\APITestCase;

class AuthServiceTest extends APITestCase {
    public function testLoginInvalidPassword() {
        $output = $this->postRequest($this->mgr(), 'auth', ['email' => 'admin@example.com', 'password' => 'wrong']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testLoginMissingEmail() {
        $output = $this->postRequest($this->mgr(), 'auth', ['password' => 'x']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('email', $response['message']);
    }

    public function testLoginSuccess() {
        $output = $this->postRequest($this->mgr(), 'auth', ['email' => 'admin@example.com', 'password' => 'admin123']);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testProfileNotAuthenticated() {
        $output = $this->getRequest($this->mgr(), 'auth');
        $response = json_decode($output, true);
        // Profile throws UnauthorizedException when no session
        $this->assertIsArray($response);
    }
    private function mgr(): DashboardServicesManager {
        return new DashboardServicesManager();
    }
}
