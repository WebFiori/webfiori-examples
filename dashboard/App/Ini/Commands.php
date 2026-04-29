<?php
namespace App\Ini;

use App\Commands\CreateUserCommand;
use App\Commands\GenerateReportCommand;
use App\Commands\ListUsersCommand;
use WebFiori\Framework\App;

class Commands {
    public static function initialize() {
        App::getRunner()->register(new ListUsersCommand());
        App::getRunner()->register(new CreateUserCommand());
        App::getRunner()->register(new GenerateReportCommand());
    }
}
