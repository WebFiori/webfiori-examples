<?php
namespace App\Ini\Routes;

use App\Apis\BillingServicesManager;
use WebFiori\Framework\Middleware\CorsMiddleware;
use WebFiori\Framework\Middleware\HttpCacheMiddleware;
use WebFiori\Framework\Middleware\RateLimitMiddleware;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

class APIsRoutes {
    public static function create() {
        // Tenant API: CORS + rate limit + session + security context
        Router::api([
            RouteOption::PATH => '/apis/{service}',
            RouteOption::TO => BillingServicesManager::class,
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
                new RateLimitMiddleware(maxRequests: 100, windowSeconds: 60),
            ]
        ]);
    }
}
