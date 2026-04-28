<?php
namespace App\Pages;

use App\Themes\BlogTheme\BlogTheme;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Ui\HTMLNode;

/**
 * Login page for admin authentication.
 */
class LoginPage extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->setTheme(BlogTheme::class);
        $this->setTitle($this->get('auth/login-title'));

        $div = new HTMLNode('div', ['class' => 'login-form']);
        $div->addChild(new HTMLNode('h2'))->text($this->get('auth/login-title'));
        $div->addChild(new HTMLNode('p', ['class' => 'error-msg', 'id' => 'login-error', 'style' => 'display:none']));

        $form = new HTMLNode('form', [
            'id' => 'login-form',
            'data-base-url' => $this->getTheme()->getBaseURL(),
            'data-error-text' => $this->get('auth/invalid-credentials')
        ]);
        $form->addChild(new HTMLNode('input', ['type' => 'email', 'id' => 'email', 'placeholder' => $this->get('auth/email'), 'required' => '']));
        $form->addChild(new HTMLNode('input', ['type' => 'password', 'id' => 'password', 'placeholder' => $this->get('auth/password'), 'required' => '']));
        $form->addChild(new HTMLNode('button', ['type' => 'submit']))->text($this->get('auth/login-btn'));

        $div->addChild($form);
        $this->insert($div);

        $this->addJS($this->getTheme()->getBaseURL().'/assets/blog-theme/js/login.js', ['defer' => '']);
    }
}
