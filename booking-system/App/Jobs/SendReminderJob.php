<?php
namespace App\Jobs;

use App\Infrastructure\Repository\AppointmentRepository;
use App\Infrastructure\Repository\UserRepository;
use App\Services\NotificationServiceInterface;
use WebFiori\Container\ContainerFacade;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Queue\Job;

/**
 * Sends a reminder SMS 24 hours before the appointment.
 */
class SendReminderJob implements Job {
    public function __construct(private int $appointmentId) {
    }

    public function getMaxAttempts(): int {
        return 3;
    }

    public function getRetryDelaySeconds(): int {
        return 60;
    }

    public function handle(): void {
        $db = new Database(App::getConfig()->getDBConnection('booking'));
        $repo = new AppointmentRepository($db);
        $appt = $repo->findById($this->appointmentId);

        if ($appt === null || $appt->status !== 'booked' || $appt->reminderSent) {
            return;
        }

        $patient = (new UserRepository($db))->findById($appt->patientId);

        if ($patient === null || empty($patient->phone)) {
            return;
        }

        /** @var NotificationServiceInterface $notifier */
        $notifier = ContainerFacade::make(NotificationServiceInterface::class);
        $notifier->sendSms($patient->phone, "Reminder: You have an appointment tomorrow at {$appt->startTime}.");

        $appt->reminderSent = true;
        $repo->save($appt);
    }
}
