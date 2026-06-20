<?php
namespace Tests;

use App\Apis\ProductServicesManager;
use WebFiori\Http\APITestCase;

/**
 * Tests for ProductService — demonstrates annotation-based API testing.
 *
 * Uses APITestCase to simulate HTTP requests without a running server.
 */
class ProductServiceTest extends APITestCase {

    private function createManager(): ProductServicesManager {
        return new ProductServicesManager();
    }

    public function testListAllProducts() {
        $output = $this->getRequest($this->createManager(), 'products');
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('products', $response['data']);
        $this->assertCount(3, $response['data']['products']);
    }

    public function testGetProductById() {
        $output = $this->getRequest($this->createManager(), 'products', [
            'id' => 1
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('product', $response['data']);
        $this->assertEquals(1, $response['data']['product']['id']);
        $this->assertEquals('Wireless Keyboard', $response['data']['product']['name']);
    }

    public function testGetProductNotFound() {
        $output = $this->getRequest($this->createManager(), 'products', [
            'id' => 999
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
        $this->assertEquals('Product not found.', $response['message']);
    }

    public function testFilterByCategory() {
        $output = $this->getRequest($this->createManager(), 'products', [
            'category' => 'Electronics'
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('products', $response['data']);
        $this->assertCount(2, $response['data']['products']);
    }

    public function testCreateProduct() {
        $output = $this->postRequest($this->createManager(), 'products', [
            'name' => 'Mechanical Keyboard',
            'category' => 'Electronics',
            'price' => 89.99,
            'in-stock' => true
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('Product created', $response['data']['message']);
        $this->assertEquals('Mechanical Keyboard', $response['data']['product']['name']);
    }

    public function testUpdateProduct() {
        $output = $this->putRequest($this->createManager(), 'products', [
            'id' => 1,
            'price' => 39.99
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('Product updated', $response['data']['message']);
    }

    public function testDeleteProduct() {
        $output = $this->deleteRequest($this->createManager(), 'products', [
            'id' => 2
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('Product deleted', $response['data']['message']);
        $this->assertEquals('Standing Desk', $response['data']['product']['name']);
    }

    public function testDeleteProductNotFound() {
        $output = $this->deleteRequest($this->createManager(), 'products', [
            'id' => 999
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
    }

    public function testInvalidParamType() {
        $output = $this->getRequest($this->createManager(), 'products', [
            'id' => 'not-a-number'
        ]);
        $response = json_decode($output, true);

        $this->assertIsArray($response);
        // Framework returns 422 for type validation failures
        $this->assertEquals('error', $response['type']);
    }
}
