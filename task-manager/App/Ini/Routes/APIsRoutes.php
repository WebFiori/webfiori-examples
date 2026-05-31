<?php

namespace App\Ini\Routes;

use App\Apis\TaskServicesManager;
use WebFiori\Framework\Middleware\RateLimitMiddleware;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

/**
 * Registers API routes for the application.
 *
 * The `{service}` path parameter is used by the framework to dispatch
 * the request to the correct {@see \WebFiori\Http\WebService} registered
 * in the {@see TaskServicesManager}. For example, a request to
 * `/apis/tasks` resolves to the service named `tasks`.
 *
 * The rate limiting middleware is applied to all API routes to prevent
 * abuse (default: 60 requests per 60-second window per client IP).
 */
class APIsRoutes {
    /**
     * Creates all API routes.
     */
    public static function create() {
        Router::api([
            RouteOption::PATH => '/apis/{service}',
            RouteOption::TO => TaskServicesManager::class,
            RouteOption::MIDDLEWARE => [new RateLimitMiddleware(
                maxRequests: 60,
                windowSeconds: 60
            )]
        ]);
    }
}
