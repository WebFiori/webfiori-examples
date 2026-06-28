<?php
namespace App\Listeners;

use App\Events\OrderPlacedEvent;
use WebFiori\Log\LoggerFacade;

class LogOrderListener {
    public function handle(OrderPlacedEvent $event): void {
        LoggerFacade::info('Order placed', [
            'order_id' => $event->orderId,
            'total' => $event->total,
            'item_count' => count($event->items),
        ]);
    }
}
