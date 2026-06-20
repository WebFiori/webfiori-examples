<?php
namespace App\Ini\Routes;

use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\ServiceRouter;

/**
 * API route registration using ServiceRouter auto-discovery.
 *
 * ServiceRouter scans the App\Apis namespace and registers routes
 * for every class annotated with #[RestController].
 */
class APIsRoutes {
    public static function create() {
        ServiceRouter::discover('App\\Apis', '/apis', [
            RouteOption::MIDDLEWARE => []
        ]);
    }
}
