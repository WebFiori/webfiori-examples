<?php
namespace App\Jobs;

use App\Domain\Order;
use App\Domain\Payment;
use App\Events\PaymentCompletedEvent;
use App\Infrastructure\Repository\OrderRepository;
use App\Infrastructure\Repository\PaymentRepository;
use App\Services\PaymentGatewayInterface;
use WebFiori\Container\ContainerFacade;
use WebFiori\Database\Database;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Framework\App;
use WebFiori\Log\FileLogger;
use WebFiori\Queue\Job;

/**
 * Job that processes payment for an order via the payment gateway.
 *
 * Retries up to 3 times with 30-second delay on failure.
 */
class ProcessPaymentJob implements Job {
    public function __construct(
        private int $orderId,
        private float $amount
    ) {
    }

    public function getMaxAttempts(): int {
        return 3;
    }

    public function getRetryDelaySeconds(): int {
        return 30;
    }

    public function handle(): void {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs', 'info');
        $db = new Database(App::getConfig()->getDBConnection('orders'));

        $orderRepo = new OrderRepository($db);
        $order = $orderRepo->findById($this->orderId);

        if ($order === null || $order->status !== Order::STATUS_PENDING) {
            $logger->info('Payment skipped: order not pending', ['order_id' => $this->orderId]);

            return;
        }

        $container = ContainerFacade::getInstance();
        /** @var PaymentGatewayInterface $gateway */
        $gateway = $container->make(PaymentGatewayInterface::class);

        $result = $gateway->charge($this->amount);

        $payment = new Payment(
            orderId: $this->orderId,
            amount: $this->amount,
            status: $result['success'] ? Payment::STATUS_COMPLETED : Payment::STATUS_FAILED,
            transactionId: $result['transactionId'],
            createdAt: date('Y-m-d H:i:s')
        );

        $paymentRepo = new PaymentRepository($db);
        $paymentRepo->save($payment);

        if ($result['success']) {
            $order->status = Order::STATUS_PAID;
            $order->updatedAt = date('Y-m-d H:i:s');
            $orderRepo->save($order);

            $logger->info('Payment completed', [
                'order_id' => $this->orderId,
                'transaction_id' => $result['transactionId'],
            ]);

            EventDispatcherFacade::dispatch(new PaymentCompletedEvent($order, $payment));
        } else {
            $logger->error('Payment failed', [
                'order_id' => $this->orderId,
                'error' => $result['error'],
            ]);

            throw new \RuntimeException('Payment failed: ' . $result['error']);
        }
    }
}
