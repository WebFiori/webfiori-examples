<?php
namespace App\Ini;

use App\Session\ArraySessionStorage;
use WebFiori\Framework\Session\DefaultSessionStorage;
use WebFiori\Framework\Session\SessionsManager;

class Privileges {
    public static function initialize(): void {
        if (getenv('APP_ENV') === 'testing') {
            SessionsManager::setStorage(new ArraySessionStorage());
        } else {
            SessionsManager::setStorage(new DefaultSessionStorage());
        }
    }
}
