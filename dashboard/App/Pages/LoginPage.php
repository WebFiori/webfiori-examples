<?php
namespace App\Pages;

use WebFiori\Framework\App;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Ui\HTMLNode;
use WebFiori\Ui\Input;
use WebFiori\Ui\Paragraph;

class LoginPage extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->setTheme(\App\Themes\LightTheme\LightTheme::class);
        $this->setTitle($this->get('nav/login'));

        $baseUrl = App::getConfig()->getBaseURL();

        $this->insert(new HTMLNode('h1'))->text($this->get('common/login-title'));

        $errMsg = new Paragraph();
        $errMsg->setID('login-error');
        $errMsg->setStyle(['color' => 'red', 'display' => 'none']);
        $this->insert($errMsg);

        $form = new HTMLNode('form', ['id' => 'login-form', 'data-base-url' => $baseUrl]);

        $emailInput = new Input('email');
        $emailInput->setID('email');
        $emailInput->setPlaceholder($this->get('common/email'));
        $emailInput->setAttribute('required', '');
        $form->addChild($emailInput);

        $passInput = new Input('password');
        $passInput->setID('password');
        $passInput->setPlaceholder($this->get('common/password'));
        $passInput->setAttribute('required', '');
        $form->addChild($passInput);

        $form->addChild(new HTMLNode('button', ['type' => 'submit']))->text($this->get('nav/login'));
        $this->insert($form);

        $this->addJS($baseUrl.'/assets/js/login.js', ['defer' => '']);
    }
}
