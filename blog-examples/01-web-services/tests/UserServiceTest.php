<?php
namespace Tests;

use App\Apis\ProductServicesManager;
use WebFiori\Http\APITestCase;

/**
 * Tests for UserService — demonstrates cross-field validation testing.
 */
class UserServiceTest extends APITestCase {

    private function createManager(): ProductServicesManager {
        return new ProductServicesManager();
    }

    public function testSuccessfulRegistration() {
        $output = $this->postRequest($this->createManager(), 'users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'securepass123',
            'password_confirm' => 'securepass123'
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('User registered', $response['data']['message']);
        $this->assertEquals('john@example.com', $response['data']['user']['email']);
    }

    public function testPasswordMismatch() {
        $output = $this->postRequest($this->createManager(), 'users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'securepass123',
            'password_confirm' => 'different'
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
    }

    public function testPasswordTooShort() {
        $output = $this->postRequest($this->createManager(), 'users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirm' => 'short'
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
    }

    public function testInvalidEmail() {
        $output = $this->postRequest($this->createManager(), 'users', [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'securepass123',
            'password_confirm' => 'securepass123'
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
    }

    public function testMissingRequiredField() {
        $output = $this->postRequest($this->createManager(), 'users', [
            'email' => 'john@example.com',
            'password' => 'securepass123',
            'password_confirm' => 'securepass123'
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
    }
}
