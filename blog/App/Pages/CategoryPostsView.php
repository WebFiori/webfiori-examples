<?php
namespace App\Pages;

use App\Infrastructure\Repository\CategoryRepository;
use App\Infrastructure\Repository\PostRepository;
use App\Themes\BlogTheme\BlogTheme;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Ui\HTMLNode;

/**
 * Displays published posts filtered by a specific category.
 */
class CategoryPostsView extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->setTheme(BlogTheme::class);

        $slug = $this->getParameterValue('slug');
        $db = new Database(App::getConfig()->getDBConnection('blog'));
        $catRepo = new CategoryRepository($db);
        $category = $catRepo->findBySlug($slug);

        if ($category === null) {
            App::getResponse()->setCode(404);
            $this->insert('p')->text('Category not found.');

            return;
        }

        $this->setTitle($category->name);
        $baseUrl = $this->getTheme()->getBaseURL();

        $this->insert(new HTMLNode('h1'))->text($category->name);

        $page = max(1, (int) App::getRequest()->getParam('page'));
        $postRepo = new PostRepository($db);
        $result = $postRepo->findPublished($page, 5, $category->id);

        foreach ($result['items'] as $post) {
            $card = new HTMLNode('article', ['class' => 'post-card']);

            $link = new HTMLNode('a', ['href' => $baseUrl.'/posts/'.$post->slug]);
            $link->text($post->title);
            $card->addChild(new HTMLNode('h2'))->addChild($link);

            $card->addChild(new HTMLNode('div', ['class' => 'post-meta']))
                ->text($this->get('blog/by').' '.($post->authorName ?? '').' — '.($post->createdAt ?? ''));

            $card->addChild(new HTMLNode('p'))
                ->text(mb_substr(strip_tags($post->content), 0, 200).'...');

            $this->insert($card);
        }

        if (empty($result['items'])) {
            $this->insert('p')->text($this->get('blog/no-posts'));
        }

        // Sidebar categories
        $aside = $this->getChildByID('side-content-area');

        if ($aside !== null) {
            foreach ($catRepo->findAll() as $c) {
                $aside->addChild(new HTMLNode('a', ['href' => $baseUrl.'/categories/'.$c->slug]))->text($c->name);
            }
        }
    }
}
