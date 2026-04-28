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
 * Home page showing a paginated list of published blog posts.
 */
class HomePageView extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->setTheme(BlogTheme::class);
        $this->setTitle($this->get('blog/title'));
        $this->setLang($this->getLangCode());

        $db = new Database(App::getConfig()->getDBConnection('blog'));
        $postRepo = new PostRepository($db);
        $catRepo = new CategoryRepository($db);

        $page = max(1, (int) App::getRequest()->getParam('page'));
        $categoryId = App::getRequest()->getParam('categoryId');
        $categoryId = $categoryId !== null ? (int) $categoryId : null;
        $result = $postRepo->findPublished($page, 5, $categoryId);

        foreach ($result['items'] as $post) {
            $card = new HTMLNode('article', ['class' => 'post-card']);

            $link = new HTMLNode('a', ['href' => $this->getTheme()->getBaseURL().'/posts/'.$post->slug]);
            $link->text($post->title);
            $card->addChild(new HTMLNode('h2'))->addChild($link);

            $card->addChild(new HTMLNode('div', ['class' => 'post-meta']))
                ->text($this->get('blog/by').' '.($post->authorName ?? '').' '.$this->get('blog/in').' '.($post->categoryName ?? '').' — '.($post->createdAt ?? ''));

            $card->addChild(new HTMLNode('p', ['class' => 'post-content']))
                ->text(mb_substr(strip_tags($post->content), 0, 200).'...');

            $readMore = new HTMLNode('a', ['class' => 'read-more', 'href' => $this->getTheme()->getBaseURL().'/posts/'.$post->slug]);
            $readMore->text($this->get('blog/read-more').' →');
            $card->addChild($readMore);

            $this->insert($card);
        }

        if (empty($result['items'])) {
            $this->insert('p')->text($this->get('blog/no-posts'));
        }

        // Pagination
        $totalPages = (int) ceil($result['total'] / 5);

        if ($totalPages > 1) {
            $pag = new HTMLNode('div', ['class' => 'pagination']);

            for ($i = 1; $i <= $totalPages; $i++) {
                if ($i === $page) {
                    $pag->addChild(new HTMLNode('span', ['class' => 'current']))->text((string) $i);
                } else {
                    $href = '?page='.$i;

                    if ($categoryId !== null) {
                        $href .= '&categoryId='.$categoryId;
                    }
                    $pag->addChild(new HTMLNode('a', ['href' => $href]))->text((string) $i);
                }
            }

            $this->insert($pag);
        }

        // Sidebar categories
        $aside = $this->getChildByID('side-content-area');

        if ($aside !== null) {
            foreach ($catRepo->findAll() as $cat) {
                $aside->addChild(new HTMLNode('a', ['href' => $this->getTheme()->getBaseURL().'/categories/'.$cat->slug]))
                    ->text($cat->name);
            }
        }
    }
}
