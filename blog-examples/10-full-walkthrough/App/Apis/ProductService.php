<?php
namespace App\Apis;

use App\Infrastructure\Repository\ProductRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\PreAuthorize;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * Product catalog API.
 *
 * GET is public. POST (create) requires 'products.manage' authority.
 */
#[RestController('products', 'Product catalog API')]
class ProductService extends WebService {
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true, description: 'Product ID')]
    public function getProducts(?int $id = null): array {
        $db = new Database(App::getConfig()->getDBConnection('orders'));
        $repo = new ProductRepository($db);

        if ($id !== null) {
            $product = $repo->findById($id);

            if ($product === null) {
                throw new NotFoundException('Product not found.');
            }

            return [$product];
        }

        return $repo->findAll();
    }

    #[PostMapping]
    #[ResponseBody]
    #[PreAuthorize("isAuthenticated() && hasAuthority('products.manage')")]
    #[RequestParam(name: 'name', type: ParamType::STRING, description: 'Product name')]
    #[RequestParam(name: 'description', type: ParamType::STRING, optional: true, default: '')]
    #[RequestParam(name: 'price', type: ParamType::DOUBLE, description: 'Product price')]
    #[RequestParam(name: 'stock', type: ParamType::INT, optional: true, default: 0)]
    public function createProduct(?string $name = null, ?string $description = null, ?float $price = null, ?int $stock = null): array {
        $db = new Database(App::getConfig()->getDBConnection('orders'));
        $repo = new ProductRepository($db);

        $product = new \App\Domain\Product(
            name: $name,
            description: $description ?? '',
            price: $price,
            stock: $stock ?? 0,
            createdAt: date('Y-m-d H:i:s')
        );

        $repo->save($product);

        return [$product];
    }
}
