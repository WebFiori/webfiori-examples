<?php
namespace App;

use WebFiori\Cache\Cache;
use WebFiori\Cache\FileStorage;

/**
 * Provides a shared cache instance for the application.
 */
class AppCache {
    private static ?Cache $instance = null;

    public static function get(): Cache {
        if (self::$instance === null) {
            $storagePath = APP_PATH.'Storage'.DS.'cache';

            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0755, true);
            }
            self::$instance = new Cache(new FileStorage($storagePath));
        }

        return self::$instance;
    }
}
