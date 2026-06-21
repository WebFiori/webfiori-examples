<?php
namespace App\Apis;

use App\Domain\Product;
use App\Infrastructure\Repository\ProductRepository;
use WebFiori\Database\Database;
use WebFiori\Database\ConnectionInfo;
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
 * Product API using the Repository pattern for data access.
 */
#[RestController('products', 'Product API with repository pattern')]
class ProductService extends WebService {

    private ProductRepository $repo;
    private Database $db;

    public function __construct(?Database $db = null) {
        parent::__construct('products');

        if ($db === null) {
            $dbPath = dirname(__DIR__, 2) . '/App/Storage/app.db';
            $connInfo = new ConnectionInfo('sqlite', '', '', $dbPath);
            $db = new Database($connInfo);
        }

        $this->db = $db;
        $this->repo = new ProductRepository($db);

        $this->addResponse('GET', '200', 'List of products or a single product')
             ->addResponse('GET', '404', 'Product not found')
             ->addResponse('POST', '200', 'Product created')
             ->addResponse('PUT', '200', 'Product updated')
             ->addResponse('PUT', '404', 'Product not found')
             ->addResponse('DELETE', '200', 'Product deleted')
             ->addResponse('DELETE', '404', 'Product not found');
    }

    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true, description: 'Product ID')]
    #[RequestParam(name: 'category', type: ParamType::STRING, optional: true, description: 'Filter by category')]
    #[RequestParam(name: 'page', type: ParamType::INT, optional: true, default: 1, description: 'Page number')]
    #[RequestParam(name: 'per-page', type: ParamType::INT, optional: true, default: 10, description: 'Items per page')]
    public function getProducts(): array {
        $id = $this->getParamVal('id');

        if ($id !== null) {
            $product = $this->repo->findById($id);
            if ($product === null) {
                throw new NotFoundException('Product not found.');
            }
            return ['product' => $this->productToArray($product)];
        }

        $category = $this->getParamVal('category');
        if ($category !== null) {
            $products = $this->repo->findByCategory($category);
            return ['products' => array_map([$this, 'productToArray'], $products)];
        }

        $page = $this->repo->paginate(
            page: $this->getParamVal('page'),
            perPage: $this->getParamVal('per-page')
        );

        return [
            'products' => array_map([$this, 'productToArray'], $page->getItems()),
            'pagination' => [
                'current_page' => $page->getCurrentPage(),
                'per_page' => $page->getPerPage(),
                'total_items' => $page->getTotalItems(),
                'total_pages' => $page->getTotalPages(),
                'has_next' => $page->hasNextPage(),
            ]
        ];
    }

    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'name', type: ParamType::STRING, description: 'Product name')]
    #[RequestParam(name: 'category', type: ParamType::STRING, description: 'Category')]
    #[RequestParam(name: 'price', type: ParamType::DOUBLE, description: 'Price')]
    #[RequestParam(name: 'stock', type: ParamType::INT, optional: true, default: 0, description: 'Stock quantity')]
    public function createProduct(): array {
        $product = new Product(
            name: $this->getParamVal('name'),
            category: $this->getParamVal('category'),
            price: $this->getParamVal('price'),
            stock: $this->getParamVal('stock')
        );

        $this->repo->save($product);
        $product->id = $this->db->getConnection()->getLastInsertId();

        return ['message' => 'Product created', 'product' => $this->productToArray($product)];
    }

    #[PutMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Product ID')]
    #[RequestParam(name: 'name', type: ParamType::STRING, optional: true, description: 'New name')]
    #[RequestParam(name: 'price', type: ParamType::DOUBLE, optional: true, description: 'New price')]
    #[RequestParam(name: 'stock', type: ParamType::INT, optional: true, description: 'New stock')]
    public function updateProduct(): array {
        $id = $this->getParamVal('id');
        $product = $this->repo->findById($id);

        if ($product === null) {
            throw new NotFoundException('Product not found.');
        }

        if ($this->getParamVal('name') !== null) {
            $product->name = $this->getParamVal('name');
        }
        if ($this->getParamVal('price') !== null) {
            $product->price = $this->getParamVal('price');
        }
        if ($this->getParamVal('stock') !== null) {
            $product->stock = $this->getParamVal('stock');
        }

        $this->repo->save($product);

        return ['message' => 'Product updated', 'product' => $this->productToArray($product)];
    }

    #[DeleteMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Product ID')]
    public function deleteProduct(): array {
        $id = $this->getParamVal('id');
        $product = $this->repo->findById($id);

        if ($product === null) {
            throw new NotFoundException('Product not found.');
        }

        $this->repo->deleteById($id);

        return ['message' => 'Product deleted'];
    }

    private function productToArray(object $product): array {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'price' => $product->price,
            'stock' => $product->stock,
        ];
    }
}
