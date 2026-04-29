<?php
namespace App\Ini\Routes;

use App\Pages\HomePage;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

class PagesRoutes {
    public static function create() {
        Router::page([
            RouteOption::PATH => '/',
            RouteOption::TO => HomePage::class,
        ]);
    }
}
