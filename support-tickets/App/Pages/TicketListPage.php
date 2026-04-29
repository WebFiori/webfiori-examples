<?php
namespace App\Pages;

use App\Infrastructure\Repository\TicketRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Ui\HTMLNode;

/**
 * Lists all tickets in a table.
 */
class TicketListPage extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->setTitle('All Tickets');
        $this->addCSS('https://cdn.jsdelivr.net/npm/water.css@2/out/water.min.css');

        $baseUrl = App::getConfig()->getBaseURL();

        $this->insert(new HTMLNode('h1'))->text('Support Tickets');
        $this->insert(new HTMLNode('a', ['href' => $baseUrl.'/submit']))->text('+ Submit New Ticket');

        $db = new Database(App::getConfig()->getDBConnection('tickets'));
        $tickets = (new TicketRepository($db))->findAll();

        $table = new HTMLNode('table');

        $thead = new HTMLNode('thead');
        $headerRow = new HTMLNode('tr');

        foreach (['#', 'Subject', 'Submitter', 'Priority', 'Status', 'Created'] as $col) {
            $headerRow->addChild(new HTMLNode('th'))->text($col);
        }

        $thead->addChild($headerRow);
        $table->addChild($thead);

        $tbody = new HTMLNode('tbody');

        foreach ($tickets as $ticket) {
            $row = new HTMLNode('tr');
            $link = new HTMLNode('a', ['href' => $baseUrl.'/tickets/'.$ticket->id]);
            $link->text((string) $ticket->id);
            $row->addChild(new HTMLNode('td'))->addChild($link);
            $row->addChild(new HTMLNode('td'))->text($ticket->subject);
            $row->addChild(new HTMLNode('td'))->text($ticket->submitterName);
            $row->addChild(new HTMLNode('td'))->text($ticket->priority);
            $row->addChild(new HTMLNode('td'))->text($ticket->status);
            $row->addChild(new HTMLNode('td'))->text($ticket->createdAt ?? '');
            $tbody->addChild($row);
        }

        $table->addChild($tbody);
        $this->insert($table);
    }
}
