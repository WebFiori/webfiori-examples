<?php
namespace App\Domain;

/**
 * Represents a line item within an order.
 */
class OrderItem {
    public function __construct(
        public ?int $id = null,
        public ?int $orderId = null,
        public ?int $productId = null,
        public int $quantity = 1,
        public float $unitPrice = 0.0
    ) {
    }
}
