<?php
namespace App\Apis;

use App\Domain\ProductCatalog;
use WebFiori\Container\ContainerFacade;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * Product catalog API — demonstrates cache-aside pattern with prefix isolation.
 */
#[RestController('products', 'Product catalog API')]
class ProductsApi extends WebService {

    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'category', type: ParamType::STRING, description: 'Filter by category', isOptional: true)]
    public function listProducts(): array {
        $catalog = ContainerFacade::make(ProductCatalog::class);
        $category = $this->getParamVal('category');

        $products = $category
            ? $catalog->getByCategory($category)
            : ContainerFacade::make(ProductCatalog::class)->getByCategory('electronics');

        return array_map(fn($p) => [
            'id'       => $p->id,
            'name'     => $p->name,
            'price'    => $p->price,
            'category' => $p->category,
        ], $products);
    }
}
