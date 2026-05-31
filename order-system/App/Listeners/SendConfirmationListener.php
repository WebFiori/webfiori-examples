<?php
namespace App\Listeners;

use App\Events\PaymentCompletedEvent;
use App\Jobs\SendOrderConfirmationJob;
use WebFiori\Queue\QueueFacade;

/**
 * Listens for PaymentCompletedEvent and queues a confirmation email.
 */
class SendConfirmationListener {
    public function handle(PaymentCompletedEvent $event): void {
        QueueFacade::dispatch(
            new SendOrderConfirmationJob($event->order->id),
            priority: 5
        );
    }
}
