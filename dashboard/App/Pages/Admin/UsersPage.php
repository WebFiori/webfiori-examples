<?php
namespace App\Pages\Admin;

use App\Infrastructure\Repository\UserRepository;
use App\Pages\BasePage;
use App\Pages\TableHelper;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Ui\HTMLNode;
use WebFiori\Ui\Input;

class UsersPage extends BasePage {
    public function __construct() {
        parent::__construct();
        $this->setTitle($this->get('common/manage-users'));
        $baseUrl = App::getConfig()->getBaseURL();

        $this->insert(new HTMLNode('h1'))->text($this->get('common/manage-users'));

        $privileges = SessionsManager::get('user-privileges') ?? [];

        if (in_array('MANAGE_USERS', $privileges)) {
            $form = new HTMLNode('form', ['id' => 'add-user', 'data-base-url' => $baseUrl, 'style' => 'display:flex;gap:0.5rem;margin-bottom:1rem;flex-wrap:wrap']);

            $nameInput = new Input('text');
            $nameInput->setID('userName');
            $nameInput->setPlaceholder($this->get('common/name'));
            $nameInput->setAttribute('required', '');
            $form->addChild($nameInput);

            $emailInput = new Input('email');
            $emailInput->setID('userEmail');
            $emailInput->setPlaceholder($this->get('common/email'));
            $emailInput->setAttribute('required', '');
            $form->addChild($emailInput);

            $passInput = new Input('password');
            $passInput->setID('userPassword');
            $passInput->setPlaceholder($this->get('common/password'));
            $passInput->setAttribute('required', '');
            $form->addChild($passInput);

            $roleSelect = new Input('select');
            $roleSelect->setID('userRole');
            $roleSelect->addOptions([
                ['value' => 'viewer', 'label' => 'Viewer'],
                ['value' => 'manager', 'label' => 'Manager'],
                ['value' => 'admin', 'label' => 'Admin'],
            ]);
            $form->addChild($roleSelect);

            $form->addChild(new HTMLNode('button', ['type' => 'submit']))->text($this->get('common/create'));
            $this->insert($form);
            $this->addJS($baseUrl.'/assets/js/users.js', ['defer' => '']);
        }

        $db = new Database(App::getConfig()->getDBConnection('dashboard'));
        $users = (new UserRepository($db))->findAll();

        $rows = [];

        foreach ($users as $u) {
            $rows[] = [(string) $u->id, $u->name, $u->email, $u->role, $u->isActive ? $this->get('common/yes') : $this->get('common/no')];
        }

        $this->insert(TableHelper::create(
            [$this->get('common/id'), $this->get('common/name'), $this->get('common/email'), $this->get('common/role'), $this->get('common/active')],
            $rows
        ));
    }
}
