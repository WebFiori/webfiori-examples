<?php
namespace App\Ini;

use App\Commands\CleanupLinksCommand;
use App\Commands\LinkStatsCommand;
use App\Commands\ListLinksCommand;
use WebFiori\Framework\App;

class Commands {
    public static function initialize() {
        App::getRunner()->register(new ListLinksCommand());
        App::getRunner()->register(new CleanupLinksCommand());
        App::getRunner()->register(new LinkStatsCommand());
    }
}
