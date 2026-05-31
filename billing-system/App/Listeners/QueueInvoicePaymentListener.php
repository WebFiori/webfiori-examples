<?php
namespace App\Listeners;

use App\Events\InvoiceCreatedEvent;
use App\Jobs\ProcessInvoicePaymentJob;
use WebFiori\Queue\QueueFacade;

class QueueInvoicePaymentListener {
    public function handle(InvoiceCreatedEvent $event): void {
        QueueFacade::dispatch(
            new ProcessInvoicePaymentJob($event->invoice->id),
            priority: 10
        );
    }
}
