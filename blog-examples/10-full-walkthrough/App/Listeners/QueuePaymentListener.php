<?php
namespace App\Listeners;

use App\Events\OrderPlacedEvent;
use App\Jobs\ProcessPaymentJob;
use WebFiori\Log\FileLogger;
use WebFiori\Queue\QueueFacade;

/**
 * Listens for OrderPlacedEvent and dispatches a payment processing job.
 */
class QueuePaymentListener {
    private FileLogger $logger;

    public function __construct() {
        $this->logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs', 'info');
    }

    public function handle(OrderPlacedEvent $event): void {
        $this->logger->info('Order placed, queuing payment', [
            'order_id' => $event->order->id,
            'total' => $event->order->total,
        ]);

        QueueFacade::dispatch(
            new ProcessPaymentJob($event->order->id, $event->order->total),
            priority: 10
        );
    }
}
