<?php
namespace App\Ini\Routes;

use App\Apis\BookingServicesManager;
use WebFiori\Framework\Middleware\CorsMiddleware;
use WebFiori\Framework\Middleware\HttpCacheMiddleware;
use WebFiori\Framework\Middleware\RateLimitMiddleware;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

class APIsRoutes {
    public static function create() {
        Router::api([
            RouteOption::PATH => '/apis/{service}',
            RouteOption::TO => BookingServicesManager::class,
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

        // Services catalog with HTTP caching
        Router::api([
            RouteOption::PATH => '/apis/services',
            RouteOption::TO => BookingServicesManager::class,
            RouteOption::MIDDLEWARE => [
                'maintenance-check',
                new CorsMiddleware(),
                new HttpCacheMiddleware(['max-age' => 600, 'public' => true]),
            ]
        ]);
    }
}
