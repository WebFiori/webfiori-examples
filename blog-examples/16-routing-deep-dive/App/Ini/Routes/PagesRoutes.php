<?php

namespace App\Ini\Routes;

use App\Pages\SwaggerPage;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

class PagesRoutes {
    public static function create() {
        Router::page([
            RouteOption::PATH => '/docs',
            RouteOption::TO => SwaggerPage::class
        ]);
    }
}
