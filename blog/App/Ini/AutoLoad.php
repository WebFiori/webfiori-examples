<?php
namespace App\Ini;

use App\Themes\BlogTheme\BlogTheme;
use WebFiori\Framework\ThemeManager;

class AutoLoad {
    /**
     * Register themes and custom autoload directories.
     */
    public static function initialize() {
        if (!ThemeManager::isThemeRegistered('Blog Theme')) {
            ThemeManager::register(new BlogTheme());
        }
    }
}
