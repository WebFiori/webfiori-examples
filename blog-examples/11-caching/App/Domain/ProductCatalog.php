<?php
namespace App\Domain;

use WebFiori\Cache\Cache;

/**
 * Product catalog service with cache-aside pattern.
 *
 * Uses prefix isolation so product cache keys never collide with other
 * application cache entries (e.g., user sessions, rate limit counters).
 */
class ProductCatalog {
    private Cache $cache;

    public function __construct(
        private ProductRepositoryInterface $repo,
        Cache $cache
    ) {
        // Scope all keys under 'products:' prefix — returns a new Cache instance
        $this->cache = $cache->withPrefix('products:');
    }

    /**
     * Returns all products for a category, served from cache when available.
     *
     * @return Product[]
     */
    public function getByCategory(string $category): array {
        return $this->cache->get(
            "category:$category",
            fn() => $this->repo->findAll($category),
            300 // 5 minutes
        );
    }

    /**
     * Returns a single product by ID, served from cache when available.
     */
    public function getById(int $id): ?Product {
        return $this->cache->get(
            "id:$id",
            fn() => $this->repo->findById($id),
            300
        );
    }

    /**
     * Invalidates all product cache entries.
     * Call after any write operation (create, update, delete).
     */
    public function invalidate(): void {
        $this->cache->flush();
    }
}
