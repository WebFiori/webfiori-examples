<?php
namespace App\Tasks;

use App\Domain\Order;
use App\Infrastructure\Repository\OrderRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Scheduler\AbstractTask;
use WebFiori\Log\FileLogger;

/**
 * Cancels orders that have been pending for more than 24 hours.
 *
 * Runs daily at midnight.
 */
class CancelExpiredOrdersTask extends AbstractTask {
    public function __construct() {
        parent::__construct('cancel-expired-orders', '0 0 * * *', 'Cancels unpaid orders older than 24 hours.');
    }

    public function afterExec(): void {
    }

    public function execute(): void {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs', 'info');
        $db = new Database(App::getConfig()->getDBConnection('orders'));
        $repo = new OrderRepository($db);

        $expired = $repo->findExpiredPending(24);

        foreach ($expired as $order) {
            $order->status = Order::STATUS_CANCELLED;
            $order->updatedAt = date('Y-m-d H:i:s');
            $repo->save($order);

            $logger->info('Expired order cancelled', ['order_id' => $order->id]);
        }

        $logger->info('Cancel expired orders task completed', ['cancelled' => count($expired)]);
    }

    public function onFail(): void {
    }

    public function onSuccess(): void {
    }
}
