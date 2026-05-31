<?php
namespace App\Listeners;

use App\Events\AppointmentCancelledEvent;
use App\Jobs\SendCancellationNoticeJob;
use WebFiori\Queue\QueueFacade;

class NotifyOnCancellationListener {
    public function handle(AppointmentCancelledEvent $event): void {
        QueueFacade::dispatch(new SendCancellationNoticeJob($event->appointment->id), priority: 10);
    }
}
