<?php

namespace App\Ini\Routes;

use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\ServiceRouter;

class APIsRoutes {
    public static function create() {
        ServiceRouter::discover('App\\Apis', '/apis', [
            RouteOption::MIDDLEWARE => []
        ]);
    }
}
