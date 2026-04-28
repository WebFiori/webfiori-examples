<?php
namespace App\Themes\BlogTheme;

use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\Theme;
use WebFiori\Ui\HeadNode;
use WebFiori\Ui\HTMLNode;

/**
 * Custom blog theme with header navigation, aside sidebar, and footer.
 *
 * Demonstrates the WebFiori theming system: implement the four abstract
 * methods to define the page structure. CSS is loaded from the theme's
 * assets directory.
 */
class BlogTheme extends Theme {
    public function __construct() {
        parent::__construct();
        $this->setName('Blog Theme');
    }

    /**
     * Returns an empty aside section. Pages that need sidebar content
     * (e.g. category links) populate it themselves.
     */
    public function getAsideNode(): HTMLNode {
        $aside = new HTMLNode();
        $aside->setClassName('sidebar');

        return $aside;
    }

    /**
     * Returns the page footer with copyright text.
     */
    public function getFooterNode(): HTMLNode {
        $footer = new HTMLNode();
        $footer->setClassName('site-footer');
        $footer->text('© '.date('Y').' WebFiori Blog Example');

        return $footer;
    }

    /**
     * Returns the page header with site title and navigation links.
     */
    public function getHeaderNode(): HTMLNode {
        $header = new HTMLNode();
        $header->setClassName('site-header');

        $title = new HTMLNode('a');
        $title->setAttribute('href', $this->getBaseURL());
        $title->setClassName('site-title');
        $title->text($this->getPage()->getTranslation()->get('blog/title'));
        $header->addChild($title);

        $nav = new HTMLNode('nav');
        $nav->setClassName('main-nav');

        $lang = $this->getPage()->getTranslation();

        $homeLink = new HTMLNode('a');
        $homeLink->setAttribute('href', $this->getBaseURL());
        $homeLink->text($lang->get('nav/home'));
        $nav->addChild($homeLink);

        // Show admin/logout if logged in, login otherwise
        SessionsManager::start('wf-session');
        $authorId = SessionsManager::get('author-id');

        if ($authorId !== null) {
            $authorName = SessionsManager::get('author-name') ?? '';

            $userInfo = new HTMLNode('span');
            $userInfo->setClassName('user-info');
            $userInfo->text($authorName);
            $nav->addChild($userInfo);

            $adminLink = new HTMLNode('a');
            $adminLink->setAttribute('href', $this->getBaseURL().'/admin');
            $adminLink->text($lang->get('nav/admin'));
            $nav->addChild($adminLink);

            $logoutLink = new HTMLNode('a');
            $logoutLink->setAttribute('href', $this->getBaseURL().'/do-logout');
            $logoutLink->text($lang->get('nav/logout'));
            $nav->addChild($logoutLink);
        } else {
            $loginLink = new HTMLNode('a');
            $loginLink->setAttribute('href', $this->getBaseURL().'/login');
            $loginLink->text($lang->get('nav/login'));
            $nav->addChild($loginLink);
        }

        // Language switcher
        $langSwitch = new HTMLNode('span');
        $langSwitch->setClassName('lang-switch');
        $enLink = new HTMLNode('a');
        $enLink->setAttribute('href', '?lang=EN');
        $enLink->text('EN');
        $arLink = new HTMLNode('a');
        $arLink->setAttribute('href', '?lang=AR');
        $arLink->text('AR');
        $langSwitch->addChild($enLink);
        $langSwitch->text(' | ');
        $langSwitch->addChild($arLink);
        $nav->addChild($langSwitch);

        $header->addChild($nav);

        return $header;
    }

    /**
     * Returns the `<head>` section with meta tags and CSS.
     */
    public function getHeadNode(): HeadNode {
        $head = new HeadNode();
        $head->addCSS($this->getBaseURL().'/assets/blog-theme/css/theme.css');

        return $head;
    }
}
