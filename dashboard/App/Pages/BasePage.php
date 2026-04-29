<?php
namespace App\Pages;

use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\Ui\WebPage;

/**
 * Helper to get the user's preferred theme class.
 */
class BasePage extends WebPage {
    public function __construct() {
        parent::__construct();
        SessionsManager::start('wf-session');
        $themePref = SessionsManager::get('theme-pref') ?? 'light';
        $themeClass = $themePref === 'dark'
            ? \App\Themes\DarkTheme\DarkTheme::class
            : \App\Themes\LightTheme\LightTheme::class;
        $this->setTheme($themeClass);
    }
}
