<?php
namespace Tests;

use App\Apis\LinkServicesManager;
use WebFiori\Http\APITestCase;

class ShortLinkServiceTest extends APITestCase {
    public function testCreateLink() {
        $output = $this->postRequest($this->mgr(), 'links', [
            'url' => 'https://example.com/test-'.time(),
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data'][0]['id']);
    }

    public function testCreateLinkDuplicate() {
        $output = $this->postRequest($this->mgr(), 'links', [
            'url' => 'https://webfiori.com',
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('abc123', $response['data'][0]['id']);
    }

    public function testCreateLinkMissingUrl() {
        $output = $this->postRequest($this->mgr(), 'links', []);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('url', $response['message']);
    }

    public function testDeleteLink() {
        // Create one to delete
        $createOutput = $this->postRequest($this->mgr(), 'links', [
            'url' => 'https://example.com/to-delete-'.time(),
        ]);
        $created = json_decode($createOutput, true);
        $id = $created['data'][0]['id'];

        $output = $this->deleteRequest($this->mgr(), 'links', ['id' => $id]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);

        // Verify deleted
        $getOutput = $this->getRequest($this->mgr(), 'links', ['id' => $id]);
        $getResponse = json_decode($getOutput, true);
        $this->assertEquals('error', $getResponse['type']);
    }

    public function testDeleteLinkMissingId() {
        $output = $this->deleteRequest($this->mgr(), 'links', []);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('id', $response['message']);
    }

    public function testDeleteLinkNotFound() {
        $output = $this->deleteRequest($this->mgr(), 'links', ['id' => 'xxxxxx']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    public function testGetLinkById() {
        $output = $this->getRequest($this->mgr(), 'links', ['id' => 'abc123']);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('abc123', $response['data'][0]['id']);
    }

    public function testGetLinkNotFound() {
        $output = $this->getRequest($this->mgr(), 'links', ['id' => 'xxxxxx']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertEquals('Link not found.', $response['message']);
    }

    public function testListAllLinks() {
        $output = $this->getRequest($this->mgr(), 'links');
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);
    }
    private function mgr(): LinkServicesManager {
        return new LinkServicesManager();
    }
}
