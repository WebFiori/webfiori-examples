<?php
namespace App\Pages;

use WebFiori\Framework\App;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Ui\HTMLNode;

/**
 * Ticket submission form page with file attachment support.
 */
class SubmitTicketPage extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->setTitle('Submit a Ticket');
        $this->addCSS('https://cdn.jsdelivr.net/npm/water.css@2/out/water.min.css');

        $baseUrl = App::getConfig()->getBaseURL();

        $this->insert(new HTMLNode('h1'))->text('Submit a Support Ticket');

        $this->insert(new HTMLNode('p', ['id' => 'form-error', 'style' => 'color:red;display:none']));
        $this->insert(new HTMLNode('p', ['id' => 'form-success', 'style' => 'color:green;display:none']));

        $form = new HTMLNode('form', [
            'id' => 'ticket-form',
            'data-base-url' => $baseUrl,
            'enctype' => 'multipart/form-data',
        ]);
        $form->addChild(new HTMLNode('input', ['type' => 'text', 'id' => 'submitterName', 'placeholder' => 'Your Name', 'required' => '']));
        $form->addChild(new HTMLNode('input', ['type' => 'email', 'id' => 'submitterEmail', 'placeholder' => 'Your Email', 'required' => '']));
        $form->addChild(new HTMLNode('input', ['type' => 'text', 'id' => 'subject', 'placeholder' => 'Subject', 'required' => '']));

        $select = new HTMLNode('select', ['id' => 'priority']);
        $select->addChild(new HTMLNode('option', ['value' => 'low']))->text('Low Priority');
        $select->addChild(new HTMLNode('option', ['value' => 'medium', 'selected' => '']))->text('Medium Priority');
        $select->addChild(new HTMLNode('option', ['value' => 'high']))->text('High Priority');
        $form->addChild($select);

        $form->addChild(new HTMLNode('textarea', ['id' => 'description', 'placeholder' => 'Describe your issue...', 'required' => '', 'style' => 'height:150px']));

        // File attachment input
        $form->addChild(new HTMLNode('label', ['for' => 'file']))->text('Attach files (pdf, doc, png, jpg, txt, zip):');
        $form->addChild(new HTMLNode('input', ['type' => 'file', 'id' => 'file', 'name' => 'file', 'multiple' => '', 'accept' => '.pdf,.doc,.docx,.png,.jpg,.jpeg,.txt,.zip']));

        $form->addChild(new HTMLNode('button', ['type' => 'submit']))->text('Submit Ticket');

        $this->insert($form);
        $this->addJS($baseUrl.'/assets/js/submit-ticket.js', ['defer' => '']);
    }
}
