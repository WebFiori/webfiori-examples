<?php
namespace Tests;

use App\Apis\BlogServicesManager;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\APITestCase;

/**
 * Tests for the Category REST API endpoints.
 */
class CategoryServiceTest extends APITestCase {
    public function testCreateCategoryMissingName() {
        $output = $this->postRequest($this->mgr(), 'categories', ['slug' => 'x']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('name', $response['message']);
    }

    public function testCreateCategoryMissingSlug() {
        $output = $this->postRequest($this->mgr(), 'categories', ['name' => 'Test']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('slug', $response['message']);
    }

    public function testCreateCategoryRequiresAuth() {
        SessionsManager::destroy();
        $output = $this->postRequest($this->mgr(), 'categories', [
            'name' => 'Test', 'slug' => 'test'
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('Not Authorized', $response['message']);
    }

    public function testListCategories() {
        $output = $this->getRequest($this->mgr(), 'categories');
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);
    }

    private function mgr(): BlogServicesManager {
        return new BlogServicesManager();
    }
}
