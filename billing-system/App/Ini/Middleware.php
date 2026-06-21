<?php
namespace App\Ini;

use App\Middleware\SecurityContextLoader;
use WebFiori\Framework\Middleware\MiddlewareManager;

class Middleware {
    public static function initialize() {
        MiddlewareManager::register(new SecurityContextLoader());
    }
}
