<?php
namespace Tests;

use App\Apis\DashboardServicesManager;
use App\Ini\Privileges;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\APITestCase;

class UserServiceTest extends APITestCase {
    private function mgr(): DashboardServicesManager {
        return new DashboardServicesManager();
    }

    private function loginAsAdmin(): void {
        SessionsManager::start('wf-session');
        SessionsManager::set('user-id', 1);
        SessionsManager::set('user-name', 'Admin User');
        SessionsManager::set('user-role', 'admin');
        SessionsManager::set('user-privileges', Privileges::privilegesForRole('admin'));
    }

    public function testListUsersRequiresAuth() {
        SessionsManager::destroy();
        $output = $this->getRequest($this->mgr(), 'users');
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testListUsersAsAdmin() {
        $this->loginAsAdmin();
        $output = $this->getRequest($this->mgr(), 'users');
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);
    }

    public function testCreateUserRequiresAdmin() {
        SessionsManager::destroy();
        $output = $this->postRequest($this->mgr(), 'users', ['name' => 'Test', 'email' => 'test@test.com', 'password' => 'pass']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testCreateUserAsAdmin() {
        $this->loginAsAdmin();
        $output = $this->postRequest($this->mgr(), 'users', [
            'name' => 'New User',
            'email' => 'new'.time().'@example.com',
            'password' => 'pass123',
            'role' => 'viewer',
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('New User', $response['data'][0]['name']);
    }

    public function testCreateUserMissingName() {
        $output = $this->postRequest($this->mgr(), 'users', ['email' => 'x@x.com', 'password' => 'x']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('name', $response['message']);
    }

    public function testUpdateUserAsAdmin() {
        $this->loginAsAdmin();
        $output = $this->putRequest($this->mgr(), 'users', ['id' => 3, 'name' => 'Updated Viewer']);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('Updated Viewer', $response['data'][0]['name']);
    }

    public function testUpdateUserMissingId() {
        $output = $this->putRequest($this->mgr(), 'users', ['name' => 'New']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('id', $response['message']);
    }

    public function testUpdateUserNotFound() {
        $this->loginAsAdmin();
        $output = $this->putRequest($this->mgr(), 'users', ['id' => 99999, 'name' => 'X']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertEquals('User not found.', $response['message']);
    }

    public function testDeactivateUserAsAdmin() {
        $this->loginAsAdmin();
        $output = $this->deleteRequest($this->mgr(), 'users', ['id' => 3]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testDeleteUserMissingId() {
        $output = $this->deleteRequest($this->mgr(), 'users', []);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('id', $response['message']);
    }
}
