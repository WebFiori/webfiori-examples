<?php
namespace App\Jobs;

use App\Infrastructure\Repository\AppointmentRepository;
use App\Infrastructure\Repository\UserRepository;
use App\Services\NotificationServiceInterface;
use WebFiori\Container\ContainerFacade;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Queue\Job;

class SendCancellationNoticeJob implements Job {
    public function __construct(private int $appointmentId) {
    }

    public function getMaxAttempts(): int {
        return 2;
    }

    public function getRetryDelaySeconds(): int {
        return 30;
    }

    public function handle(): void {
        $db = new Database(App::getConfig()->getDBConnection('booking'));
        $appt = (new AppointmentRepository($db))->findById($this->appointmentId);

        if ($appt === null) {
            return;
        }

        $patient = (new UserRepository($db))->findById($appt->patientId);

        if ($patient === null || empty($patient->phone)) {
            return;
        }

        /** @var NotificationServiceInterface $notifier */
        $notifier = ContainerFacade::make(NotificationServiceInterface::class);
        $notifier->sendSms($patient->phone, "Your appointment on {$appt->startTime} has been cancelled.");
    }
}
