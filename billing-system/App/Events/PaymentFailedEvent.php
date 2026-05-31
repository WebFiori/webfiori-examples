<?php
namespace App\Events;

use App\Domain\Invoice;

class PaymentFailedEvent {
    public function __construct(public readonly Invoice $invoice, public readonly string $error) {
    }
}
