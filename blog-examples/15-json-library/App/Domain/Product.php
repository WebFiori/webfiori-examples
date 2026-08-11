<?php
namespace App\Domain;

use WebFiori\Json\JsonIgnore;
use WebFiori\Json\JsonProperty;
use WebFiori\Json\JsonType;

/**
 * A product with attributes demonstrating JsonProperty, JsonIgnore, and auto-mapping.
 */
class Product {
    #[JsonProperty('product_id')]
    public int $id;

    #[JsonIgnore]
    public string $internalCode;

    private string $name;
    private float $price;
    private string $category;

    public function __construct(int $id, string $name, float $price, string $category) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->category = $category;
        $this->internalCode = 'INT-' . $id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getCategory(): string {
        return $this->category;
    }

    #[JsonIgnore]
    public function getInternalMargin(): float {
        return 0.35;
    }
}
