<?php
namespace App\Domain;

/**
 * Contract for retrieving products.
 */
interface ProductRepositoryInterface {
    /**
     * Returns all products, optionally filtered by category.
     *
     * @return Product[]
     */
    public function findAll(?string $category = null): array;

    public function findById(int $id): ?Product;
}
