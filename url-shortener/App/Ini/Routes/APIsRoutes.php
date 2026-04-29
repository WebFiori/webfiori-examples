<?php
namespace App\Ini\Routes;

use App\Apis\LinkServicesManager;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

class APIsRoutes {
    public static function create() {
        Router::api([
            RouteOption::PATH => '/apis/{service}',
            RouteOption::TO => LinkServicesManager::class,
        ]);
    }
}
