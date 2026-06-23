<?php
namespace App\Domain;

/**
 * Processes orders using injected dependencies.
 */
class OrderService {
    public function __construct(
        private PaymentGatewayInterface $gateway,
        private NotifierInterface $notifier
    ) {
    }

    /**
     * Charge the customer and send a confirmation.
     *
     * @return array{transaction_id: string, message: string}
     */
    public function processPayment(float $amount, string $orderId): array {
        $transactionId = $this->gateway->charge($amount);
        $this->notifier->send("Order $orderId paid. Transaction: $transactionId");

        return [
            'transaction_id' => $transactionId,
            'message' => "Order $orderId processed successfully",
        ];
    }
}
