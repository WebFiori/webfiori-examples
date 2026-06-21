<?php
namespace App\Policies;

use App\Domain\Appointment;

/**
 * Patients can only view their own appointments.
 * Providers can view appointments assigned to them.
 * Admins can view all.
 */
class AppointmentViewPolicy {
    public function getPermission(): string {
        return 'appointments.view';
    }

    public function evaluate($user, ?object $resource = null): bool {
        if ($resource === null) {
            return true;
        }

        if (!$resource instanceof Appointment) {
            return true;
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
