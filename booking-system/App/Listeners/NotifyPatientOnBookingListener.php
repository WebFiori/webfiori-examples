<?php
namespace App\Listeners;

use App\Events\AppointmentBookedEvent;
use App\Jobs\SendBookingConfirmationJob;
use WebFiori\Queue\QueueFacade;

class NotifyPatientOnBookingListener {
    public function handle(AppointmentBookedEvent $event): void {
        QueueFacade::dispatch(new SendBookingConfirmationJob($event->appointment->id), priority: 5);
    }
}
