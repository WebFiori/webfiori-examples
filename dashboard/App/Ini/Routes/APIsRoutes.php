<?php
namespace App\Ini\Routes;

use App\Apis\DashboardServicesManager;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

class APIsRoutes {
    public static function create() {
        Router::api([
            RouteOption::PATH => '/apis/{service}',
            RouteOption::TO => DashboardServicesManager::class,
            RouteOption::MIDDLEWARE => ['start-session', 'audit-log'],
        ]);
    }
}
