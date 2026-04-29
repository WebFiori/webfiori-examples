<?php
namespace App\Pages;

use App\Infrastructure\Repository\AttachmentRepository;
use App\Infrastructure\Repository\ReplyRepository;
use App\Infrastructure\Repository\TicketRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Ui\HTMLNode;

/**
 * Displays ticket details with replies and a reply form.
 */
class TicketDetailPage extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->addCSS('https://cdn.jsdelivr.net/npm/water.css@2/out/water.min.css');

        $ticketId = $this->getParameterValue('id');
        $db = new Database(App::getConfig()->getDBConnection('tickets'));
        $ticket = (new TicketRepository($db))->findById((int) $ticketId);

        if ($ticket === null) {
            App::getResponse()->setCode(404);
            $this->insert('p')->text('Ticket not found.');

            return;
        }

        $baseUrl = App::getConfig()->getBaseURL();
        $this->setTitle('Ticket #'.$ticket->id.' — '.$ticket->subject);

        // Ticket info
        $this->insert(new HTMLNode('h1'))->text('Ticket #'.$ticket->id);
        $info = new HTMLNode('div');
        $info->addChild(new HTMLNode('p'))->text('Subject: '.$ticket->subject);
        $info->addChild(new HTMLNode('p'))->text('Status: '.$ticket->status.' | Priority: '.$ticket->priority);
        $info->addChild(new HTMLNode('p'))->text('From: '.$ticket->submitterName.' ('.$ticket->submitterEmail.')');
        $info->addChild(new HTMLNode('p'))->text('Created: '.$ticket->createdAt);
        $info->addChild(new HTMLNode('p'))->text($ticket->description);
        $this->insert($info);

        // Attachments
        $attachments = (new AttachmentRepository($db))->findByTicketId($ticket->id);

        if (!empty($attachments)) {
            $this->insert(new HTMLNode('h3'))->text('Attachments');

            foreach ($attachments as $att) {
                $link = new HTMLNode('a', ['href' => $baseUrl.'/apis/attachments?id='.$att->id]);
                $link->text($att->fileName.' ('.round($att->fileSize / 1024, 1).' KB)');
                $this->insert(new HTMLNode('p'))->addChild($link);
            }
        }

        // Replies
        $replies = (new ReplyRepository($db))->findByTicketId($ticket->id);
        $this->insert(new HTMLNode('h3'))->text('Replies ('.count($replies).')');

        foreach ($replies as $reply) {
            $div = new HTMLNode('div', ['style' => 'border-left:3px solid #2980b9;padding:0.5rem 1rem;margin:0.5rem 0']);
            $div->addChild(new HTMLNode('strong'))->text($reply->authorName);
            $div->addChild(new HTMLNode('small'))->text(' — '.$reply->createdAt);
            $div->addChild(new HTMLNode('p'))->text($reply->content);
            $this->insert($div);
        }

        // Reply form
        $this->insert(new HTMLNode('h4'))->text('Add a Reply');

        $form = new HTMLNode('form', ['id' => 'reply-form', 'data-base-url' => $baseUrl, 'data-ticket-id' => (string) $ticket->id]);
        $form->addChild(new HTMLNode('input', ['type' => 'text', 'id' => 'authorName', 'placeholder' => 'Your Name', 'required' => '']));
        $form->addChild(new HTMLNode('textarea', ['id' => 'replyContent', 'placeholder' => 'Your reply...', 'required' => '', 'style' => 'height:100px']));
        $form->addChild(new HTMLNode('button', ['type' => 'submit']))->text('Submit Reply');

        $this->insert($form);
        $this->addJS($baseUrl.'/assets/js/ticket-detail.js', ['defer' => '']);
    }
}
