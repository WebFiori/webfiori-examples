<?php
namespace Tests;

use App\Apis\FinanceServicesManager;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\APITestCase;

class AuthServiceTest extends APITestCase {
    public function testLoginInvalidPassword() {
        $output = $this->postRequest($this->mgr(), 'auth', ['email' => 'demo@example.com', 'password' => 'wrong']);
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
        $output = $this->postRequest($this->mgr(), 'auth', ['email' => 'demo@example.com', 'password' => 'demo123']);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testProfileNotAuthenticated() {
        SessionsManager::destroy();
        $output = $this->getRequest($this->mgr(), 'auth');
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testRegister() {
        $output = $this->postRequest($this->mgr(), 'auth', [
            'email' => 'new'.time().'@example.com',
            'password' => 'pass123',
            'name' => 'New User',
            'register' => 'true',
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }
    private function mgr(): FinanceServicesManager {
        return new FinanceServicesManager();
    }
}
