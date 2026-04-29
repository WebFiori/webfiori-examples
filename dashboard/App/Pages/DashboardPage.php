<?php
namespace App\Pages;

use App\Infrastructure\Repository\ProjectRepository;
use App\Infrastructure\Repository\UserRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Ui\HTMLNode;

class DashboardPage extends BasePage {
    public function __construct() {
        parent::__construct();
        $this->setTitle($this->get('nav/dashboard'));

        $baseUrl = App::getConfig()->getBaseURL();
        $db = new Database(App::getConfig()->getDBConnection('dashboard'));
        $users = (new UserRepository($db))->findAll();
        $projects = (new ProjectRepository($db))->findAllWithOwner();
        $active = array_filter($projects, fn ($p) => $p->status === 'active');

        $this->insert(new HTMLNode('h1'))->text($this->get('nav/dashboard'));
        $this->insert(new HTMLNode('p'))->text($this->get('common/welcome').', '.SessionsManager::get('user-name').' ('.SessionsManager::get('user-role').')');

        $stats = new HTMLNode('div', ['style' => 'display:flex;gap:1rem;margin:1rem 0']);

        $userCard = new HTMLNode('a', ['href' => $baseUrl.'/admin/users', 'style' => 'flex:1;padding:1.5rem;background:#3498db;color:#fff;border-radius:8px;text-decoration:none;text-align:center']);
        $userCard->addChild(new HTMLNode('div', ['style' => 'font-size:2rem;font-weight:bold']))->text((string) count($users));
        $userCard->addChild(new HTMLNode('div'))->text($this->get('nav/users'));
        $stats->addChild($userCard);

        $projCard = new HTMLNode('a', ['href' => $baseUrl.'/projects', 'style' => 'flex:1;padding:1.5rem;background:#2ecc71;color:#fff;border-radius:8px;text-decoration:none;text-align:center']);
        $projCard->addChild(new HTMLNode('div', ['style' => 'font-size:2rem;font-weight:bold']))->text((string) count($projects));
        $projCard->addChild(new HTMLNode('div'))->text($this->get('nav/projects'));
        $stats->addChild($projCard);

        $activeCard = new HTMLNode('a', ['href' => $baseUrl.'/projects', 'style' => 'flex:1;padding:1.5rem;background:#e67e22;color:#fff;border-radius:8px;text-decoration:none;text-align:center']);
        $activeCard->addChild(new HTMLNode('div', ['style' => 'font-size:2rem;font-weight:bold']))->text((string) count($active));
        $activeCard->addChild(new HTMLNode('div'))->text($this->get('common/active'));
        $stats->addChild($activeCard);

        $reportCard = new HTMLNode('a', ['href' => $baseUrl.'/reports', 'style' => 'flex:1;padding:1.5rem;background:#9b59b6;color:#fff;border-radius:8px;text-decoration:none;text-align:center']);
        $reportCard->addChild(new HTMLNode('div'))->text($this->get('nav/reports'));
        $stats->addChild($reportCard);

        $this->insert($stats);
    }
}
