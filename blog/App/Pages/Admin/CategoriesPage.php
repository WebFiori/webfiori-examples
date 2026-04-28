<?php
namespace App\Pages\Admin;

use App\Infrastructure\Repository\CategoryRepository;
use App\Themes\BlogTheme\BlogTheme;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Ui\HTMLNode;

/**
 * Admin page for managing blog categories.
 */
class CategoriesPage extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->setTheme(BlogTheme::class);
        $this->setTitle($this->get('admin/manage-categories'));

        $baseUrl = $this->getTheme()->getBaseURL();

        $this->insert(new HTMLNode('h1'))->text($this->get('admin/manage-categories'));

        $db = new Database(App::getConfig()->getDBConnection('blog'));
        $categories = (new CategoryRepository($db))->findAll();

        $table = new HTMLNode('table', ['class' => 'admin-table']);

        $thead = new HTMLNode('thead');
        $headerRow = new HTMLNode('tr');

        foreach ([$this->get('admin/title'), $this->get('admin/slug'), ''] as $col) {
            $headerRow->addChild(new HTMLNode('th'))->text($col);
        }

        $thead->addChild($headerRow);
        $table->addChild($thead);

        $tbody = new HTMLNode('tbody');

        foreach ($categories as $cat) {
            $row = new HTMLNode('tr');
            $row->addChild(new HTMLNode('td'))->text($cat->name);
            $row->addChild(new HTMLNode('td'))->text($cat->slug);
            $row->addChild(new HTMLNode('td'))->text($cat->description);
            $tbody->addChild($row);
        }

        $table->addChild($tbody);
        $this->insert($table);

        $this->insert('br');
        $this->insert(new HTMLNode('h3'))->text($this->get('admin/manage-categories'));

        $form = new HTMLNode('form', [
            'method' => 'POST',
            'action' => $baseUrl.'/apis/categories',
            'class' => 'comment-form'
        ]);
        $form->addChild(new HTMLNode('input', ['type' => 'hidden', 'name' => 'service', 'value' => 'categories']));
        $form->addChild(new HTMLNode('input', ['type' => 'text', 'name' => 'name', 'placeholder' => $this->get('admin/title'), 'required' => '']));
        $form->addChild(new HTMLNode('input', ['type' => 'text', 'name' => 'slug', 'placeholder' => $this->get('admin/slug'), 'required' => '']));
        $form->addChild(new HTMLNode('input', ['type' => 'text', 'name' => 'description', 'placeholder' => 'Description']));
        $form->addChild(new HTMLNode('button', ['type' => 'submit']))->text($this->get('admin/save'));

        $this->insert($form);
    }
}
