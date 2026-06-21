<?php
namespace App\Ini;

class AutoLoad {
    public static function initialize() {
        $root = dirname(__DIR__, 2);
        \spl_autoload_register(function ($className) use ($root) {
            $classPath = $root . DS . str_replace('\\', DS, $className) . '.php';

            if (file_exists($classPath)) {
                require_once $classPath;
            }
        });
    }
}
