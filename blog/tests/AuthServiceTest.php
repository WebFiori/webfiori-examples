<?php
namespace Tests;

use App\Apis\BlogServicesManager;
use WebFiori\Http\APITestCase;

/**
 * Tests for the Auth and Comment REST API endpoints.
 */
class AuthServiceTest extends APITestCase {
    public function testAddComment() {
        $output = $this->postRequest($this->mgr(), 'comments', [
            'postId' => 1,
            'authorName' => 'Tester',
            'authorEmail' => 'tester@example.com',
            'content' => 'Nice post!'
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testAddCommentMissingFields() {
        $output = $this->postRequest($this->mgr(), 'comments', ['postId' => 1]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('missing', $response['message']);
    }

    public function testAddCommentToDraftPost() {
        $output = $this->postRequest($this->mgr(), 'comments', [
            'postId' => 5,
            'authorName' => 'Tester',
            'authorEmail' => 'tester@example.com',
            'content' => 'Hello'
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testAddCommentToNonExistentPost() {
        $output = $this->postRequest($this->mgr(), 'comments', [
            'postId' => 99999,
            'authorName' => 'Tester',
            'authorEmail' => 'tester@example.com',
            'content' => 'Hello'
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertEquals('Post not found.', $response['message']);
    }

    public function testLoginInvalidEmail() {
        $output = $this->postRequest($this->mgr(), 'auth', [
            'email' => 'nobody@example.com',
            'password' => 'admin123'
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertEquals('Invalid email or password.', $response['message']);
    }

    public function testLoginInvalidPassword() {
        $output = $this->postRequest($this->mgr(), 'auth', [
            'email' => 'admin@example.com',
            'password' => 'wrong'
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertEquals('Invalid email or password.', $response['message']);
    }

    public function testLoginMissingEmail() {
        $output = $this->postRequest($this->mgr(), 'auth', ['password' => 'x']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('email', $response['message']);
    }

    public function testLoginMissingPassword() {
        $output = $this->postRequest($this->mgr(), 'auth', ['email' => 'admin@example.com']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('password', $response['message']);
    }

    public function testLoginSuccess() {
        $output = $this->postRequest($this->mgr(), 'auth', [
            'email' => 'admin@example.com',
            'password' => 'admin123'
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('Admin', $response['data'][0]['name']);
    }

    private function mgr(): BlogServicesManager {
        return new BlogServicesManager();
    }
}
