<?php
namespace App\Pages;

use App\Infrastructure\Repository\ShortLinkRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Ui\HTMLNode;

/**
 * Home page with a form to shorten URLs and a list of recent links.
 */
class HomePage extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->setTitle('URL Shortener');
        $this->addCSS('https://cdn.jsdelivr.net/npm/water.css@2/out/water.min.css');

        $baseUrl = App::getConfig()->getBaseURL();

        $this->insert(new HTMLNode('h1'))->text('URL Shortener');

        // Shorten form
        $form = new HTMLNode('form', [
            'id' => 'shorten-form',
            'data-base-url' => $baseUrl,
        ]);
        $form->addChild(new HTMLNode('input', ['type' => 'url', 'id' => 'url', 'placeholder' => 'Paste a long URL here...', 'required' => '', 'style' => 'width:70%;display:inline']));
        $form->addChild(new HTMLNode('button', ['type' => 'submit', 'style' => 'display:inline']))->text('Shorten');
        $this->insert($form);

        $this->insert(new HTMLNode('p', ['id' => 'result', 'style' => 'display:none']));

        // Recent links table
        $this->insert(new HTMLNode('h2'))->text('Recent Links');

        $db = new Database(App::getConfig()->getDBConnection('shortener'));
        $links = (new ShortLinkRepository($db))->findAll();

        $table = new HTMLNode('table');
        $thead = new HTMLNode('thead');
        $headerRow = new HTMLNode('tr');

        foreach (['Code', 'Original URL', 'Clicks', 'Created'] as $col) {
            $headerRow->addChild(new HTMLNode('th'))->text($col);
        }

        $thead->addChild($headerRow);
        $table->addChild($thead);

        $tbody = new HTMLNode('tbody');

        foreach ($links as $link) {
            $row = new HTMLNode('tr');
            $row->addChild(new HTMLNode('td'))
                ->addChild(new HTMLNode('a', ['href' => $baseUrl.'/'.$link->id]))->text($link->id);
            $row->addChild(new HTMLNode('td'))->text(mb_substr($link->redirectTo, 0, 60).(strlen($link->redirectTo) > 60 ? '...' : ''));
            $row->addChild(new HTMLNode('td'))->text((string) $link->numberOfClicks);
            $row->addChild(new HTMLNode('td'))->text($link->createdAt ?? '');
            $tbody->addChild($row);
        }

        $table->addChild($tbody);
        $this->insert($table);

        $this->addJS($baseUrl.'/assets/js/home.js', ['defer' => '']);
    }
}
