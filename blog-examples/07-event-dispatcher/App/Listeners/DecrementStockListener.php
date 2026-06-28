<?php
namespace App\Listeners;

use App\Events\OrderPlacedEvent;

/**
 * Simulates decrementing stock for ordered items.
 */
class DecrementStockListener {
    private array $decremented = [];

    public function handle(OrderPlacedEvent $event): void {
        foreach ($event->items as $item) {
            $this->decremented[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ];
        }
    }

    public function getDecremented(): array {
        return $this->decremented;
    }
}
