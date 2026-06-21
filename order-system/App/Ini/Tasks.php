<?php
namespace App\Ini;

use App\Tasks\CancelExpiredOrdersTask;
use WebFiori\Framework\Scheduler\TasksManager;

class Tasks {
    public static function initialize() {
        TasksManager::scheduleTask(new CancelExpiredOrdersTask());
    }
}
