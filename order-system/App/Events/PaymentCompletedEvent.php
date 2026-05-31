<?php
namespace App\Events;

use App\Domain\Order;
use App\Domain\Payment;

/**
 * Dispatched when a payment is completed successfully.
 */
class PaymentCompletedEvent {
    public function __construct(
        public readonly Order $order,
        public readonly Payment $payment
    ) {
    }
}
