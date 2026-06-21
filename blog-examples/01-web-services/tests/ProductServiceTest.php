<?php
namespace Tests;

use App\Apis\ProductService;
use WebFiori\Http\Test\ServiceTestCase;

/**
 * Tests for ProductService — demonstrates annotation-based API testing.
 *
 * Uses ServiceTestCase to test services directly without a WebServicesManager.
 */
class ProductServiceTest extends ServiceTestCase {

    public function testListAllProducts() {
        $this->get(new ProductService())
            ->assertOk()
            ->assertJsonHas('data');
    }

    public function testGetProductById() {
        $response = $this->get(new ProductService(), ['id' => 1]);
        $response->assertOk();

        $json = $response->getJson();
        $this->assertEquals(1, $json['data']['product']['id']);
        $this->assertEquals('Wireless Keyboard', $json['data']['product']['name']);
    }

    public function testGetProductNotFound() {
        $this->get(new ProductService(), ['id' => 999])
            ->assertNotFound()
            ->assertError();
    }

    public function testFilterByCategory() {
        $response = $this->get(new ProductService(), ['category' => 'Electronics']);
        $response->assertOk();

        $json = $response->getJson();
        $this->assertCount(2, $json['data']['products']);
    }

    public function testCreateProduct() {
        $response = $this->post(new ProductService(), [
            'name' => 'Mechanical Keyboard',
            'category' => 'Electronics',
            'price' => 89.99,
            'in-stock' => true
        ]);
        $response->assertOk();

        $json = $response->getJson();
        $this->assertEquals('Product created', $json['data']['message']);
        $this->assertEquals('Mechanical Keyboard', $json['data']['product']['name']);
    }

    public function testUpdateProduct() {
        $response = $this->put(new ProductService(), [
            'id' => 1,
            'price' => 39.99
        ]);
        $response->assertOk();

        $json = $response->getJson();
        $this->assertEquals('Product updated', $json['data']['message']);
    }

    public function testDeleteProduct() {
        $response = $this->delete(new ProductService(), ['id' => 2]);
        $response->assertOk();

        $json = $response->getJson();
        $this->assertEquals('Product deleted', $json['data']['message']);
        $this->assertEquals('Standing Desk', $json['data']['product']['name']);
    }

    public function testDeleteProductNotFound() {
        $this->delete(new ProductService(), ['id' => 999])
            ->assertNotFound()
            ->assertError();
    }

    public function testInvalidParamType() {
        $this->get(new ProductService(), ['id' => 'not-a-number'])
            ->assertError();
    }
}
