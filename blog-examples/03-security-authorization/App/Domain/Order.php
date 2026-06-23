<?php
namespace App\Domain;

/**
 * Order entity for demonstrating resource-level authorization.
 */
class Order {
    public function __construct(
        public int $id,
        public int $userId,
        public float $total,
        public string $status = 'pending'
    ) {
    }
}
