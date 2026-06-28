<?php
namespace App\Ini\Routes;

use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\ServiceRouter;

/**
 * API routes — services are auto-discovered from App\Apis.
 */
class APIsRoutes {
    public static function create() {
        ServiceRouter::discover('App\\Apis', '/apis', [
            RouteOption::MIDDLEWARE => [
                'start-session',
                'security-context',
            ]
        ]);
    }
}
