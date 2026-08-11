<?php
namespace App\Ini\Routes;

use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Router\ServiceRouter;

class APIsRoutes {
    public static function create() {
        // Discover all services in App\Apis, recursively including subdirectories.
        // Subdirectory names become path segments: App\Apis\Admin\ -> /apis/admin/...
        ServiceRouter::discover('App\\Apis', '/apis', [], null, true);

        // Closure route with optional parameter
        Router::closure([
            RouteOption::PATH => '/news/{category}/{slug?}',
            RouteOption::TO   => function () {
                $category = \WebFiori\Framework\Router\Router::getParameterValue('category');
                $slug     = \WebFiori\Framework\Router\Router::getParameterValue('slug');

                \WebFiori\Framework\App::getResponse()->addHeader('Content-Type', 'application/json');
                \WebFiori\Framework\App::getResponse()->write(json_encode([
                    'category' => $category,
                    'slug'     => $slug,
                ]));
            },
            RouteOption::REQUEST_METHODS => ['GET'],
        ]);
    }
}
