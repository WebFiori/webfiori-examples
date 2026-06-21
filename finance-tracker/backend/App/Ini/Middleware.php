<?php
namespace App\Ini;

use App\Middleware\AuthMiddleware;
use App\Middleware\CorsMiddleware;
use WebFiori\Framework\Middleware\MiddlewareManager;

class Middleware {
    public static function initialize() {
        MiddlewareManager::register(new CorsMiddleware());
        MiddlewareManager::register(new AuthMiddleware());
    }
}
