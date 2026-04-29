<?php
namespace App\Pages;

use App\Infrastructure\Repository\ProjectRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Ui\Anchor;
use WebFiori\Ui\HTMLNode;
use WebFiori\Ui\Input;

class ProjectsPage extends BasePage {
    public function __construct() {
        parent::__construct();
        $this->setTitle($this->get('nav/projects'));
        $baseUrl = App::getConfig()->getBaseURL();

        $this->insert(new HTMLNode('h1'))->text($this->get('nav/projects'));

        $privileges = SessionsManager::get('user-privileges') ?? [];

        if (in_array('CREATE_PROJECT', $privileges)) {
            $form = new HTMLNode('form', ['id' => 'create-project', 'data-base-url' => $baseUrl, 'style' => 'display:flex;gap:0.5rem;margin-bottom:1rem;flex-wrap:wrap']);

            $nameInput = new Input('text');
            $nameInput->setID('projName');
            $nameInput->setPlaceholder($this->get('common/name'));
            $nameInput->setAttribute('required', '');
            $nameInput->setStyle(['flex' => '1']);
            $form->addChild($nameInput);

            $descInput = new Input('text');
            $descInput->setID('projDesc');
            $descInput->setPlaceholder($this->get('common/description'));
            $descInput->setStyle(['flex' => '2']);
            $form->addChild($descInput);

            $form->addChild(new HTMLNode('button', ['type' => 'submit']))->text($this->get('common/create'));
            $this->insert($form);
            $this->addJS($baseUrl.'/assets/js/projects.js', ['defer' => '']);
        }

        $db = new Database(App::getConfig()->getDBConnection('dashboard'));
        $projects = (new ProjectRepository($db))->findAllWithOwner();

        $rows = [];

        foreach ($projects as $p) {
            $rows[] = [
                new Anchor($baseUrl.'/projects/'.$p->id, $p->name),
                $p->status,
                $p->ownerName ?? '',
                $p->createdAt ?? '',
            ];
        }

        $this->insert(TableHelper::create(
            [$this->get('common/name'), $this->get('common/status'), $this->get('common/owner'), $this->get('common/created')],
            $rows
        ));
    }
}
