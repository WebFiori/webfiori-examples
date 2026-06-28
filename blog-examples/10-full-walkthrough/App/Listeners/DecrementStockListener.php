<?php
namespace App\Listeners;

use App\Events\OrderPlacedEvent;
use App\Infrastructure\Repository\ProductRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Log\FileLogger;

/**
 * Listens for OrderPlacedEvent and decrements product stock.
 */
class DecrementStockListener {
    private FileLogger $logger;

    public function __construct() {
        $this->logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs', 'info');
    }

    public function handle(OrderPlacedEvent $event): void {
        $db = new Database(App::getConfig()->getDBConnection('orders'));
        $repo = new ProductRepository($db);

        foreach ($event->items as $item) {
            $repo->decrementStock($item->productId, $item->quantity);
            $this->logger->info('Stock decremented', [
                'product_id' => $item->productId,
                'quantity' => $item->quantity,
            ]);
        }
    }
}
