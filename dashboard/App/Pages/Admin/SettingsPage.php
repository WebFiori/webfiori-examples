<?php
namespace App\Pages\Admin;

use App\Pages\BasePage;
use WebFiori\Framework\App;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Ui\HTMLNode;

class SettingsPage extends BasePage {
    public function __construct() {
        parent::__construct();
        $this->setTitle($this->get('nav/settings'));
        $baseUrl = App::getConfig()->getBaseURL();

        // Handle theme change
        $theme = App::getRequest()->getParam('theme');

        if ($theme !== null && in_array($theme, ['light', 'dark'])) {
            SessionsManager::set('theme-pref', $theme);
            App::getResponse()->addHeader('Location', $baseUrl.'/admin/settings');
            App::getResponse()->setCode(302);

            return;
        }

        $this->insert(new HTMLNode('h1'))->text($this->get('nav/settings'));

        $pageUrl = Router::getRouteUri()->getUri();
        // Theme
        $this->insert(new HTMLNode('h3'))->text($this->get('common/theme'));
        $this->insert(new HTMLNode('a', ['href' => $pageUrl.'?theme=light']))->text($this->get('common/light'));
        $this->insert(new HTMLNode('span'))->text(' | ');
        $this->insert(new HTMLNode('a', ['href' => $pageUrl.'?theme=dark']))->text($this->get('common/dark'));
        $this->insert(new HTMLNode('p'))->text($this->get('common/current-theme').': '.(SessionsManager::get('theme-pref') ?? 'light'));

        // Language
        $this->insert(new HTMLNode('h3'))->text($this->get('common/language'));
        $this->insert(new HTMLNode('a', ['href' => $pageUrl.'?lang=EN']))->text('English');
        $this->insert(new HTMLNode('span'))->text(' | ');
        $this->insert(new HTMLNode('a', ['href' => $pageUrl.'?lang=AR']))->text('العربية');
    }
}
