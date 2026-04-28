<?php
namespace App\Ini\Routes;

use App\Apis\BlogServicesManager;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

/**
 * Registers all API routes for the blog.
 */
class APIsRoutes {
    public static function create() {
        Router::api([
            RouteOption::PATH => '/apis/{service}',
            RouteOption::TO => BlogServicesManager::class,
            RouteOption::MIDDLEWARE => ['start-session']
        ]);
    }
}
