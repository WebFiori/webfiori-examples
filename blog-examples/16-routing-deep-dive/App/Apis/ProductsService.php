<?php
namespace App\Apis;

use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

/**
 * Route: GET /apis/products
 *
 * Class name 'ProductsService' strips 'Service' suffix and converts to kebab-case.
 * Result: 'products'
 */
#[RestController('products', 'Product catalog API')]
class ProductsService extends WebService {

    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function listProducts(): array {
        return [
            ['id' => 1, 'name' => 'Laptop Pro',    'price' => 1299.99],
            ['id' => 2, 'name' => 'Wireless Mouse', 'price' => 29.99],
        ];
    }
}
