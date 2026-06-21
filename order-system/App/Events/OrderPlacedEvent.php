<?php
namespace App\Events;

use App\Domain\Order;

/**
 * Dispatched when a new order is placed.
 */
class OrderPlacedEvent {
    public function __construct(
        public readonly Order $order,
        public readonly array $items
    ) {
    }
}
