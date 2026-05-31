<?php
namespace App\Ini\Routes;

use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;
use WebFiori\Log\FileLogger;

class ClosureRoutes {
    public static function create() {
        // Assign only 'mw-e'. Its dependencies (D→C→B→A) are declared via
        // getDependencies(). The framework resolves and includes the full chain
        // automatically when they are registered globally.
        //
        // NOTE: The framework's sortByDependencies only SORTS middleware that
        // are already assigned. It does NOT auto-pull dependencies.
        // Therefore we assign all 5 explicitly, but in REVERSE order to prove
        // the framework re-orders them correctly based on dependencies.
        Router::closure([
            RouteOption::PATH => '/test-chain',
            RouteOption::TO => function () {
                $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs');
                $logger->info('Route::handler');
                echo json_encode(['message' => 'Chain executed']);
            },
            RouteOption::MIDDLEWARE => ['mw-e', 'mw-d', 'mw-c', 'mw-b', 'mw-a']
        ]);
    }
}
