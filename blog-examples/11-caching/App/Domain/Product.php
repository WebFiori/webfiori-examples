<?php
namespace App\Domain;

/**
 * Represents a product in the catalog.
 */
class Product {
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly float $price,
        public readonly string $category
    ) {
    }
}
