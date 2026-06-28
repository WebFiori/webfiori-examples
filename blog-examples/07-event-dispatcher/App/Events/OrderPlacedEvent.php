<?php
namespace App\Events;

class OrderPlacedEvent {
    public function __construct(
        public readonly int $orderId,
        public readonly float $total,
        public readonly array $items
    ) {}
}
