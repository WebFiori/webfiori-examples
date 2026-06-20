<?php
namespace App\Domain;

/**
 * Domain entity representing a product.
 *
 * Plain PHP class with no framework dependencies.
 */
class Product {
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $category = '',
        public float $price = 0,
        public bool $inStock = true
    ) {}

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setCategory(string $category): void {
        $this->category = $category;
    }

    public function setPrice(float $price): void {
        $this->price = $price;
    }

    public function setInStock(bool $inStock): void {
        $this->inStock = $inStock;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'price' => $this->price,
            'inStock' => $this->inStock,
        ];
    }
}
