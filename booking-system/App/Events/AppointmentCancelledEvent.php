<?php
namespace App\Events;

use App\Domain\Appointment;

class AppointmentCancelledEvent {
    public function __construct(public readonly Appointment $appointment) {
    }
}
