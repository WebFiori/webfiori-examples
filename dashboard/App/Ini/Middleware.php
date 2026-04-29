<?php
namespace App\Ini;

use App\Middleware\AuditLogMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\RefreshUserProfileMiddleware;
use App\Middleware\RoleCheckMiddleware;
use WebFiori\Framework\Middleware\MiddlewareManager;

class Middleware {
    public static function initialize() {
        MiddlewareManager::register(new AuthMiddleware());
        MiddlewareManager::register(new RefreshUserProfileMiddleware());
        MiddlewareManager::register(new RoleCheckMiddleware());
        MiddlewareManager::register(new AuditLogMiddleware());
    }
}
