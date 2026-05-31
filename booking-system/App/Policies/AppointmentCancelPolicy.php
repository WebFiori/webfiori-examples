<?php
namespace App\Policies;

use App\Domain\Appointment;

/**
 * Patients can cancel their own booked appointments.
 * Providers can cancel appointments assigned to them.
 * Admins can cancel any.
 */
class AppointmentCancelPolicy {
    public function getPermission(): string {
        return 'appointments.cancel';
    }

    public function evaluate($user, ?object $resource = null): bool {
        if ($resource === null || !$resource instanceof Appointment) {
            return false;
        }

        if ($resource->status !== Appointment::STATUS_BOOKED) {
            return false;
        }

        $role = $user->getRoles()[0] ?? '';

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'provider') {
            return $user->getId() === $resource->providerId;
        }

        return $user->getId() === $resource->patientId;
    }
}
