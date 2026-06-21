<?php
namespace App\Listeners;

use App\Events\PaymentFailedEvent;
use WebFiori\Log\FileLogger;

class LogPaymentFailureListener {
    public function handle(PaymentFailedEvent $event): void {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs', 'error');
        $logger->error('Payment failed', [
            'invoice_id' => $event->invoice->id,
            'tenant_id' => $event->invoice->tenantId,
            'amount' => $event->invoice->amount,
            'error' => $event->error,
        ]);
    }
}
