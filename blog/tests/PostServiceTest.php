<?php
namespace Tests;

use App\Apis\BlogServicesManager;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\APITestCase;

/**
 * Tests for the Post REST API endpoints.
 */
class PostServiceTest extends APITestCase {
    public function testCreatePostMissingTitle() {
        $output = $this->postRequest($this->mgr(), 'posts', ['slug' => 'x']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('title', $response['message']);
    }

    public function testCreatePostRequiresAuth() {
        // Ensure no active session
        SessionsManager::destroy();
        $output = $this->postRequest($this->mgr(), 'posts', [
            'title' => 'Test', 'slug' => 'test', 'content' => 'body'
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('Not Authorized', $response['message']);
    }

    public function testDeletePostMissingId() {
        $output = $this->deleteRequest($this->mgr(), 'posts', []);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('id', $response['message']);
    }

    public function testDeletePostRequiresAuth() {
        SessionsManager::destroy();
        $output = $this->deleteRequest($this->mgr(), 'posts', ['id' => 1]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testFilterByCategory() {
        $output = $this->getRequest($this->mgr(), 'posts', ['categoryId' => 1]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testGetPostById() {
        $output = $this->getRequest($this->mgr(), 'posts', ['id' => 1]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals(1, $response['data'][0]['id']);
    }

    public function testGetPostNotFound() {
        $output = $this->getRequest($this->mgr(), 'posts', ['id' => 99999]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertEquals('Post not found.', $response['message']);
    }

    public function testListPublishedPosts() {
        $output = $this->getRequest($this->mgr(), 'posts');
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
    }

    public function testUpdatePostMissingId() {
        $output = $this->putRequest($this->mgr(), 'posts', ['title' => 'New']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('id', $response['message']);
    }

    public function testUpdatePostRequiresAuth() {
        SessionsManager::destroy();
        $output = $this->putRequest($this->mgr(), 'posts', ['id' => 1, 'title' => 'New']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    private function mgr(): BlogServicesManager {
        return new BlogServicesManager();
    }
}
