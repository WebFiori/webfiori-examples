<?php
namespace Tests;

use App\Apis\ProductService;
use WebFiori\Http\Test\ServiceTestCase;

class ProductRepositoryTest extends ServiceTestCase {

    public function testCreateProduct() {
        $response = $this->post(new ProductService(), [
            'name' => 'Keyboard',
            'category' => 'Electronics',
            'price' => 49.99,
            'stock' => 100
        ]);
        $response->assertOk();
        $json = $response->getJson();
        $this->assertEquals('Product created', $json['data']['message']);
        $this->assertEquals('Keyboard', $json['data']['product']['name']);
    }

    public function testGetAllProducts() {
        // Create a product first
        $this->post(new ProductService(), [
            'name' => 'Mouse',
            'category' => 'Electronics',
            'price' => 29.99,
            'stock' => 50
        ]);

        $response = $this->get(new ProductService(), []);
        $response->assertOk();
        $json = $response->getJson();
        $this->assertArrayHasKey('products', $json['data']);
        $this->assertArrayHasKey('pagination', $json['data']);
    }

    public function testGetProductById() {
        $create = $this->post(new ProductService(), [
            'name' => 'Monitor',
            'category' => 'Electronics',
            'price' => 299.99,
            'stock' => 20
        ]);
        $id = $create->getJson()['data']['product']['id'];

        $response = $this->get(new ProductService(), ['id' => $id]);
        $response->assertOk();
        $json = $response->getJson();
        $this->assertEquals('Monitor', $json['data']['product']['name']);
    }

    public function testGetProductNotFound() {
        $this->get(new ProductService(), ['id' => 99999])
            ->assertNotFound();
    }

    public function testFilterByCategory() {
        $this->post(new ProductService(), [
            'name' => 'Desk',
            'category' => 'Furniture',
            'price' => 199.99,
            'stock' => 10
        ]);

        $response = $this->get(new ProductService(), ['category' => 'Furniture']);
        $response->assertOk();
        $json = $response->getJson();
        $this->assertNotEmpty($json['data']['products']);
        foreach ($json['data']['products'] as $p) {
            $this->assertEquals('Furniture', $p['category']);
        }
    }

    public function testUpdateProduct() {
        $create = $this->post(new ProductService(), [
            'name' => 'Laptop',
            'category' => 'Electronics',
            'price' => 999.99,
            'stock' => 5
        ]);
        $id = $create->getJson()['data']['product']['id'];

        $response = $this->put(new ProductService(), [
            'id' => $id,
            'price' => 899.99
        ]);
        $response->assertOk();
        $this->assertEquals(899.99, $response->getJson()['data']['product']['price']);
    }

    public function testDeleteProduct() {
        $create = $this->post(new ProductService(), [
            'name' => 'Tablet',
            'category' => 'Electronics',
            'price' => 499.99,
            'stock' => 15
        ]);
        $id = $create->getJson()['data']['product']['id'];

        $this->delete(new ProductService(), ['id' => $id])->assertOk();

        // Verify deleted
        $this->get(new ProductService(), ['id' => $id])->assertNotFound();
    }

    public function testPagination() {
        // Create multiple products
        for ($i = 1; $i <= 5; $i++) {
            $this->post(new ProductService(), [
                'name' => "Item $i",
                'category' => 'Bulk',
                'price' => $i * 10,
                'stock' => $i
            ]);
        }

        $response = $this->get(new ProductService(), ['page' => 1, 'per-page' => 2]);
        $response->assertOk();
        $json = $response->getJson();
        $this->assertLessThanOrEqual(2, count($json['data']['products']));
        $this->assertArrayHasKey('total_items', $json['data']['pagination']);
    }
}
