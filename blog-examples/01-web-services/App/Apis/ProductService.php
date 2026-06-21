<?php
namespace App\Apis;

use App\Domain\Product;
use WebFiori\Http\Annotations\AllowAnonymous;
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
 *
 * Features covered:
 * - #[RestController] for auto-discovery
 * - #[GetMapping], #[PostMapping], #[PutMapping], #[DeleteMapping]
 * - #[RequestParam] for input validation
 * - #[ResponseBody] for JSON serialization
 */
#[RestController('products', 'Product management API')]
class ProductService extends WebService {

    public function __construct() {
        parent::__construct('products');

        $this->addResponse('GET', '200', 'List of products or a single product by ID')
             ->addResponse('GET', '404', 'Product not found')
             ->addResponse('POST', '200', 'Product created successfully')
             ->addResponse('PUT', '200', 'Product updated successfully')
             ->addResponse('PUT', '404', 'Product not found')
             ->addResponse('DELETE', '200', 'Product deleted successfully')
             ->addResponse('DELETE', '404', 'Product not found');
    }

    /**
     * In-memory store for demonstration purposes.
     * In a real app, this would be a repository.
     */
    private static array $products = [
        1 => ['id' => 1, 'name' => 'Wireless Keyboard', 'category' => 'Electronics', 'price' => 49.99, 'inStock' => true],
        2 => ['id' => 2, 'name' => 'Standing Desk', 'category' => 'Furniture', 'price' => 399.00, 'inStock' => true],
        3 => ['id' => 3, 'name' => 'USB-C Hub', 'category' => 'Electronics', 'price' => 29.99, 'inStock' => false],
    ];

    /**
     * List all products or get one by ID.
     */
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true, description: 'Product ID')]
    #[RequestParam(name: 'category', type: ParamType::STRING, optional: true, description: 'Filter by category')]
    public function getProducts(): array {
        $id = $this->getParamVal('id');

        if ($id !== null) {
            if (!isset(self::$products[$id])) {
                throw new NotFoundException('Product not found.');
            }

            return ['product' => self::$products[$id]];
        }

        $category = $this->getParamVal('category');
        $products = self::$products;

        if ($category !== null) {
            $products = array_filter($products, fn($p) => strcasecmp($p['category'], $category) === 0);
        }

        return ['products' => array_values($products)];
    }

    /**
     * Create a new product.
     *
     * Demonstrates #[RequestParam] with POST for creating resources.
     */
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'name', type: ParamType::STRING, description: 'Product name')]
    #[RequestParam(name: 'category', type: ParamType::STRING, description: 'Product category')]
    #[RequestParam(name: 'price', type: ParamType::DOUBLE, description: 'Product price')]
    #[RequestParam(name: 'in-stock', type: ParamType::BOOL, optional: true, default: true, description: 'Stock status')]
    public function createProduct(): array {
        $nextId = empty(self::$products) ? 1 : max(array_keys(self::$products)) + 1;

        $product = new Product(
            id: $nextId,
            name: $this->getParamVal('name'),
            category: $this->getParamVal('category'),
            price: $this->getParamVal('price'),
            inStock: $this->getParamVal('in-stock') ?? true
        );

        self::$products[$nextId] = $product->toArray();

        return ['message' => 'Product created', 'product' => $product->toArray()];
    }

    /**
     * Update an existing product.
     */
    #[PutMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Product ID')]
    #[RequestParam(name: 'name', type: ParamType::STRING, optional: true, description: 'New name')]
    #[RequestParam(name: 'price', type: ParamType::DOUBLE, optional: true, description: 'New price')]
    #[RequestParam(name: 'in-stock', type: ParamType::BOOL, optional: true, description: 'Stock status')]
    public function updateProduct(): array {
        $id = $this->getParamVal('id');

        if (!isset(self::$products[$id])) {
            throw new NotFoundException('Product not found.');
        }

        $name = $this->getParamVal('name');
        $price = $this->getParamVal('price');
        $inStock = $this->getParamVal('in-stock');

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

    /**
     * Delete a product by ID.
     */
    #[DeleteMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Product ID')]
    public function deleteProduct(): array {
        $id = $this->getParamVal('id');

        if (!isset(self::$products[$id])) {
            throw new NotFoundException('Product not found.');
        }

        $product = self::$products[$id];
        unset(self::$products[$id]);

        return ['message' => 'Product deleted', 'product' => $product];
    }
}
