<?php
namespace App\Events;

use App\Domain\Invoice;

class InvoiceCreatedEvent {
    public function __construct(public readonly Invoice $invoice) {
    }
}
