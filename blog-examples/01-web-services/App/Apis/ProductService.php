<?php
namespace App\Apis;

use App\Domain\Product;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\ApiResponse;
use WebFiori\Http\Annotations\DeleteMapping;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\PutMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * Product REST API demonstrating annotation-based web services.
 */
#[RestController('products', 'Product management API')]
class ProductService extends WebService {

    private static array $products = [
        1 => ['id' => 1, 'name' => 'Wireless Keyboard', 'category' => 'Electronics', 'price' => 49.99, 'inStock' => true],
        2 => ['id' => 2, 'name' => 'Standing Desk', 'category' => 'Furniture', 'price' => 399.00, 'inStock' => true],
        3 => ['id' => 3, 'name' => 'USB-C Hub', 'category' => 'Electronics', 'price' => 29.99, 'inStock' => false],
    ];

    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true, description: 'Product ID')]
    #[RequestParam(name: 'category', type: ParamType::STRING, optional: true, description: 'Filter by category')]
    #[ApiResponse(status: '200', description: 'List of products or a single product by ID')]
    #[ApiResponse(status: '404', description: 'Product not found')]
    public function getProducts(?int $id = null, ?string $category = null): array {

        if ($id !== null) {
            if (!isset(self::$products[$id])) {
                throw new NotFoundException('Product not found.');
            }
            return ['product' => self::$products[$id]];
        }

        $products = self::$products;

        if ($category !== null) {
            $products = array_filter($products, fn($p) => strcasecmp($p['category'], $category) === 0);
        }

        return ['products' => array_values($products)];
    }

    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'name', type: ParamType::STRING, description: 'Product name')]
    #[RequestParam(name: 'category', type: ParamType::STRING, description: 'Product category')]
    #[RequestParam(name: 'price', type: ParamType::DOUBLE, description: 'Product price')]
    #[RequestParam(name: 'in-stock', type: ParamType::BOOL, optional: true, default: true, description: 'Stock status')]
    #[ApiResponse(status: '200', description: 'Product created successfully')]
    public function createProduct(string $name, string $category, float $price, bool $inStock = true): array {
        $nextId = empty(self::$products) ? 1 : max(array_keys(self::$products)) + 1;

        $product = new Product(
            id: $nextId,
            name: $name,
            category: $category,
            price: $price,
            inStock: $inStock
        );

        self::$products[$nextId] = $product->toArray();

        return ['message' => 'Product created', 'product' => $product->toArray()];
    }

    #[PutMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Product ID')]
    #[RequestParam(name: 'name', type: ParamType::STRING, optional: true, description: 'New name')]
    #[RequestParam(name: 'price', type: ParamType::DOUBLE, optional: true, description: 'New price')]
    #[RequestParam(name: 'in-stock', type: ParamType::BOOL, optional: true, description: 'Stock status')]
    #[ApiResponse(status: '200', description: 'Product updated successfully')]
    #[ApiResponse(status: '404', description: 'Product not found')]
    public function updateProduct(int $id, ?string $name = null, ?float $price = null, ?bool $inStock = null): array {

        if (!isset(self::$products[$id])) {
            throw new NotFoundException('Product not found.');
        }

        if ($name !== null) {
            self::$products[$id]['name'] = $name;
        }
        if ($price !== null) {
            self::$products[$id]['price'] = $price;
        }
        if ($inStock !== null) {
            self::$products[$id]['inStock'] = $inStock;
        }

        return ['message' => 'Product updated', 'product' => self::$products[$id]];
    }

    #[DeleteMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Product ID')]
    #[ApiResponse(status: '200', description: 'Product deleted successfully')]
    #[ApiResponse(status: '404', description: 'Product not found')]
    public function deleteProduct(int $id): array {

        if (!isset(self::$products[$id])) {
            throw new NotFoundException('Product not found.');
        }

        $product = self::$products[$id];
        unset(self::$products[$id]);

        return ['message' => 'Product deleted', 'product' => $product];
    }
}
