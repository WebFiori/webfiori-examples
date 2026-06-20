<?php
namespace Tests;

use App\Apis\UserService;
use WebFiori\Http\Test\ServiceTestCase;

/**
 * Tests for UserService — demonstrates cross-field validation testing.
 */
class UserServiceTest extends ServiceTestCase {

    public function testSuccessfulRegistration() {
        $response = $this->post(new UserService(), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'securepass123',
            'password_confirm' => 'securepass123'
        ]);
        $response->assertOk();

        $json = $response->getJson();
        $this->assertEquals('User registered', $json['data']['message']);
        $this->assertEquals('john@example.com', $json['data']['user']['email']);
    }

    public function testPasswordMismatch() {
        $this->post(new UserService(), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'securepass123',
            'password_confirm' => 'different'
        ])->assertError();
    }

    public function testPasswordTooShort() {
        $this->post(new UserService(), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirm' => 'short'
        ])->assertError();
    }

    public function testInvalidEmail() {
        $this->post(new UserService(), [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'securepass123',
            'password_confirm' => 'securepass123'
        ])->assertError();
    }

    public function testMissingRequiredField() {
        $this->post(new UserService(), [
            'email' => 'john@example.com',
            'password' => 'securepass123',
            'password_confirm' => 'securepass123'
        ])->assertError();
    }
}
