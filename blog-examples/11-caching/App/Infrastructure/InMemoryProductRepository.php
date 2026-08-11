<?php
namespace App\Infrastructure;

use App\Domain\Product;
use App\Domain\ProductRepositoryInterface;

/**
 * In-memory product repository — simulates a database.
 * Tracks how many times it was queried so tests can verify cache hits.
 */
class InMemoryProductRepository implements ProductRepositoryInterface {
    private int $queryCount = 0;

    private array $products = [
        ['id' => 1, 'name' => 'Laptop Pro',     'price' => 1299.99, 'category' => 'electronics'],
        ['id' => 2, 'name' => 'Wireless Mouse',  'price' => 29.99,   'category' => 'electronics'],
        ['id' => 3, 'name' => 'Standing Desk',   'price' => 499.00,  'category' => 'furniture'],
        ['id' => 4, 'name' => 'Ergonomic Chair', 'price' => 349.00,  'category' => 'furniture'],
        ['id' => 5, 'name' => 'USB-C Hub',       'price' => 49.99,   'category' => 'electronics'],
    ];

    public function findAll(?string $category = null): array {
        $this->queryCount++;
        $rows = $category
            ? array_filter($this->products, fn($p) => $p['category'] === $category)
            : $this->products;

        return array_values(array_map(fn($p) => new Product($p['id'], $p['name'], $p['price'], $p['category']), $rows));
    }

    public function findById(int $id): ?Product {
        $this->queryCount++;

        foreach ($this->products as $p) {
            if ($p['id'] === $id) {
                return new Product($p['id'], $p['name'], $p['price'], $p['category']);
            }
        }

        return null;
    }

    public function getQueryCount(): int {
        return $this->queryCount;
    }

    public function resetQueryCount(): void {
        $this->queryCount = 0;
    }
}
