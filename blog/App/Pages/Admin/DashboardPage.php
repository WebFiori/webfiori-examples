<?php
namespace App\Pages\Admin;

use App\Infrastructure\Repository\PostRepository;
use App\Themes\BlogTheme\BlogTheme;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Ui\HTMLNode;

/**
 * Admin dashboard showing all posts with edit/delete actions.
 */
class DashboardPage extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->setTheme(BlogTheme::class);
        $this->setTitle($this->get('admin/dashboard'));

        $baseUrl = $this->getTheme()->getBaseURL();

        $this->insert(new HTMLNode('h1'))->text($this->get('admin/manage-posts'));
        $this->insert(new HTMLNode('a', ['class' => 'btn btn-success', 'href' => $baseUrl.'/admin/posts/create']))
            ->text('+ '.$this->get('admin/create-post'));
        $this->insert('br');

        $db = new Database(App::getConfig()->getDBConnection('blog'));
        $posts = (new PostRepository($db))->findAllWithDetails();

        $table = new HTMLNode('table', ['class' => 'admin-table']);

        $thead = new HTMLNode('thead');
        $headerRow = new HTMLNode('tr');

        foreach ([$this->get('admin/title'), $this->get('admin/category'), $this->get('admin/status'), $this->get('admin/actions')] as $col) {
            $headerRow->addChild(new HTMLNode('th'))->text($col);
        }

        $thead->addChild($headerRow);
        $table->addChild($thead);

        $tbody = new HTMLNode('tbody');

        foreach ($posts as $post) {
            $row = new HTMLNode('tr');
            $row->addChild(new HTMLNode('td'))->text($post->title);
            $row->addChild(new HTMLNode('td'))->text($post->categoryName ?? '—');
            $row->addChild(new HTMLNode('td'))->text($post->status === 'published' ? $this->get('blog/published') : $this->get('blog/draft'));
            $row->addChild(new HTMLNode('td'))
                ->addChild(new HTMLNode('a', ['class' => 'btn btn-primary', 'href' => $baseUrl.'/admin/posts/'.$post->id.'/edit']))
                ->text($this->get('admin/edit'));
            $tbody->addChild($row);
        }

        $table->addChild($tbody);
        $this->insert($table);
    }
}
