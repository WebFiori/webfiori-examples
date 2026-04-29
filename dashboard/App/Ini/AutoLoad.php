<?php
namespace App\Ini;

use App\Themes\DarkTheme\DarkTheme;
use App\Themes\LightTheme\LightTheme;
use WebFiori\Framework\ThemeManager;

class AutoLoad {
    public static function initialize() {
        if (!ThemeManager::isThemeRegistered('Light Theme')) {
            ThemeManager::register(new LightTheme());
        }

        if (!ThemeManager::isThemeRegistered('Dark Theme')) {
            ThemeManager::register(new DarkTheme());
        }
    }
}
