<?php
namespace App\Ini\Routes;

use App\Apis\OrderServicesManager;
use WebFiori\Framework\Middleware\CorsMiddleware;
use WebFiori\Framework\Middleware\HttpCacheMiddleware;
use WebFiori\Framework\Middleware\RateLimitMiddleware;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

/**
 * API routes with middleware groups: CORS, rate limiting, maintenance check.
 */
class APIsRoutes {
    public static function create() {
        // Main API routes with 'api' middleware group
        Router::api([
            RouteOption::PATH => '/apis/{service}',
            RouteOption::TO => OrderServicesManager::class,
            RouteOption::MIDDLEWARE => [
                'maintenance-check',
                'start-session',
                'security-context',
                new CorsMiddleware([
                    'origins' => ['*'],
                    'methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                    'headers' => ['Content-Type', 'Authorization', 'X-CSRF-TOKEN'],
                    'credentials' => true,
                ]),
                new RateLimitMiddleware(maxRequests: 60, windowSeconds: 60),
            ]
        ]);

        // Product catalog with HTTP caching (ETag/304)
        Router::api([
            RouteOption::PATH => '/apis/products',
            RouteOption::TO => OrderServicesManager::class,
            RouteOption::MIDDLEWARE => [
                'maintenance-check',
                new CorsMiddleware(),
                new RateLimitMiddleware(maxRequests: 120, windowSeconds: 60),
                new HttpCacheMiddleware(['max-age' => 300, 'public' => true]),
            ]
        ]);
    }
}
