<?php
namespace App\Tasks;

use App\Infrastructure\Repository\AppointmentRepository;
use App\Jobs\SendReminderJob;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Scheduler\AbstractTask;
use WebFiori\Log\FileLogger;
use WebFiori\Queue\QueueFacade;

/**
 * Dispatches reminder jobs for appointments starting within 24 hours.
 * Runs every hour.
 */
class DispatchRemindersTask extends AbstractTask {
    public function __construct() {
        parent::__construct('dispatch-reminders', '0 * * * *', 'Queue reminders for upcoming appointments.');
    }

    public function afterExec(): void {
    }

    public function execute(): void {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs', 'info');
        $db = new Database(App::getConfig()->getDBConnection('booking'));
        $repo = new AppointmentRepository($db);

        $upcoming = $repo->findUpcomingUnreminded();

        foreach ($upcoming as $appt) {
            QueueFacade::dispatch(new SendReminderJob($appt->id), priority: 3);
        }

        $logger->info('Reminders dispatched', ['count' => count($upcoming)]);
    }

    public function onFail(): void {
    }

    public function onSuccess(): void {
    }
}
