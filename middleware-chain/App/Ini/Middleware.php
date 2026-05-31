<?php
namespace App\Ini;

use App\Middleware\MiddlewareA;
use App\Middleware\MiddlewareB;
use App\Middleware\MiddlewareC;
use App\Middleware\MiddlewareD;
use App\Middleware\MiddlewareE;
use WebFiori\Framework\Middleware\MiddlewareManager;

class Middleware {
    public static function initialize() {
        // Register all middleware so the framework knows about them.
        // Only MiddlewareE will be assigned to the route — the framework
        // should resolve D, C, B, A automatically via getDependencies().
        MiddlewareManager::register(new MiddlewareA());
        MiddlewareManager::register(new MiddlewareB());
        MiddlewareManager::register(new MiddlewareC());
        MiddlewareManager::register(new MiddlewareD());
        MiddlewareManager::register(new MiddlewareE());
    }
}
