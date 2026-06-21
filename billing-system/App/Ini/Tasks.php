<?php
namespace App\Ini;

use App\Tasks\ExpireSubscriptionsTask;
use WebFiori\Framework\Scheduler\TasksManager;

class Tasks {
    public static function initialize() {
        TasksManager::scheduleTask(new ExpireSubscriptionsTask());
    }
}
