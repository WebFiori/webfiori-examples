<?php

namespace App\Ini\Routes;

use App\Apis\TaskServicesManager;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

/**
 * Registers API routes for the application.
 *
 * The `{service}` path parameter is used by the framework to dispatch
 * the request to the correct {@see \WebFiori\Http\WebService} registered
 * in the {@see TaskServicesManager}. For example, a request to
 * `/apis/tasks` resolves to the service named `tasks`.
 */
class APIsRoutes {
    /**
     * Creates all API routes.
     */
    public static function create() {
        Router::api([
            RouteOption::PATH => '/apis/{service}',
            RouteOption::TO => TaskServicesManager::class
        ]);
    }
}
