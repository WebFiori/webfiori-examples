<?php
namespace App\Events;

use App\Domain\Appointment;

class AppointmentBookedEvent {
    public function __construct(public readonly Appointment $appointment) {
    }
}
