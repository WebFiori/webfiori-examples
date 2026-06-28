<?php
namespace App\Domain;

/**
 * Represents a product in the catalog.
 */
class Product {
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $description = '',
        public float $price = 0.0,
        public int $stock = 0,
        public ?string $createdAt = null
    ) {
    }
}
