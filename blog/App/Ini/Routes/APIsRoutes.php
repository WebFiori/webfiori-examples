<?php
namespace App\Ini\Routes;

use App\Apis\BlogServicesManager;
use WebFiori\Framework\Middleware\RateLimitMiddleware;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

/**
 * Registers all API routes for the blog.
 *
 * Rate limiting is applied to prevent abuse (30 requests per 60 seconds per IP).
 */
class APIsRoutes {
    public static function create() {
        Router::api([
            RouteOption::PATH => '/apis/{service}',
            RouteOption::TO => BlogServicesManager::class,
            RouteOption::MIDDLEWARE => [
                'start-session',
                new RateLimitMiddleware(maxRequests: 30, windowSeconds: 60)
            ]
        ]);
    }
}
