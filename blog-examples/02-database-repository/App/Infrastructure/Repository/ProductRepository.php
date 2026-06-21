<?php
namespace App\Infrastructure\Repository;

use App\Domain\Product;
use WebFiori\Database\Database;
use WebFiori\Database\Repository\AbstractRepository;

/**
 * Product repository — handles all database operations for Product entities.
 */
class ProductRepository extends AbstractRepository {

    public function __construct(Database $db) {
        parent::__construct($db);
    }

    protected function getTableName(): string {
        return 'products';
    }

    protected function getIdField(): string {
        return 'id';
    }

    protected function toEntity(array $row): object {
        $product = new Product();
        $product->id = (int) $row['id'];
        $product->name = $row['name'];
        $product->category = $row['category'];
        $product->price = (float) $row['price'];
        $product->stock = (int) $row['stock'];
        return $product;
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'category' => $entity->category,
            'price' => $entity->price,
            'stock' => $entity->stock,
        ];
    }

    /**
     * Find products by category.
     */
    public function findByCategory(string $category): array {
        $result = $this->getDatabase()->table($this->getTableName())
            ->select()
            ->where('category', $category)
            ->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    /**
     * Find products with stock below a threshold.
     */
    public function findLowStock(int $threshold = 10): array {
        $result = $this->getDatabase()->table($this->getTableName())
            ->select()
            ->where('stock', $threshold, '<')
            ->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }
}
