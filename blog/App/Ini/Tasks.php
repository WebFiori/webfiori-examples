<?php
namespace App\Ini;

use App\Tasks\WeeklyDigestTask;
use WebFiori\Framework\Scheduler\TasksManager;

class Tasks {
    /**
     * A method that can be used to register background tasks.
     *
     **/
    public static function initialize() {
        TasksManager::scheduleTask(new WeeklyDigestTask());
    }
}
