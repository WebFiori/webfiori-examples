<?php
namespace App\Themes\LightTheme;

use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\Theme;
use WebFiori\Ui\HeadNode;
use WebFiori\Ui\HTMLNode;

class LightTheme extends Theme {
    public function __construct() {
        parent::__construct();
        $this->setName('Light Theme');
    }

    public function getAsideNode(): HTMLNode {
        return new HTMLNode();
    }

    public function getFooterNode(): HTMLNode {
        $footer = new HTMLNode('footer');
        $url = Router::getRouteUri()->getUri();
        $footer->text('© '.date('Y').' Dashboard Example | ');
        $footer->addChild(new HTMLNode('a', ['href' => $url.'?lang=EN']))->text('EN');
        $footer->text(' | ');
        $footer->addChild(new HTMLNode('a', ['href' => $url.'?lang=AR']))->text('AR');

        return $footer;
    }

    public function getHeaderNode(): HTMLNode {
        $header = new HTMLNode('nav');
        $lang = $this->getPage()->getTranslation();
        $base = $this->getBaseURL();

        SessionsManager::start('wf-session');
        $userId = SessionsManager::get('user-id');
        $role = SessionsManager::get('user-role') ?? '';

        $links = [['/', $lang->get('nav/dashboard')]];

        if ($userId !== null) {
            $links[] = ['/projects', $lang->get('nav/projects')];
            $links[] = ['/reports', $lang->get('nav/reports')];

            if ($role === 'admin') {
                $links[] = ['/admin/users', $lang->get('nav/users')];
                $links[] = ['/admin/audit-log', $lang->get('nav/audit-log')];
                $links[] = ['/admin/settings', $lang->get('nav/settings')];
            }

            $links[] = ['/do-logout', $lang->get('nav/logout')];
        } else {
            $links[] = ['/login', $lang->get('nav/login')];
        }

        foreach ($links as [$path, $text]) {
            $header->addChild(new HTMLNode('a', ['href' => $base.$path]))->text($text.' ');
        }

        return $header;
    }

    public function getHeadNode(): HeadNode {
        $head = new HeadNode();
        $head->addCSS('https://cdn.jsdelivr.net/npm/water.css@2/out/light.min.css');
        $head->addCSS($this->getBaseURL().'/assets/css/dashboard.css');

        return $head;
    }
}
