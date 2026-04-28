<?php
namespace App\Pages;

use App\Infrastructure\Repository\CategoryRepository;
use App\Infrastructure\Repository\CommentRepository;
use App\Infrastructure\Repository\PostRepository;
use App\Themes\BlogTheme\BlogTheme;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Ui\HTMLNode;

/**
 * Displays a single blog post with its comments and a comment form.
 */
class PostDetailView extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->setTheme(BlogTheme::class);

        $slug = $this->getParameterValue('slug');
        $db = new Database(App::getConfig()->getDBConnection('blog'));
        $postRepo = new PostRepository($db);
        $post = $postRepo->findBySlug($slug);

        if ($post === null || $post->status !== 'published') {
            App::getResponse()->setCode(404);
            $this->insert('p')->text('Post not found.');

            return;
        }

        $this->setTitle($post->title);
        $baseUrl = $this->getTheme()->getBaseURL();

        $article = new HTMLNode('article', ['class' => 'post-full']);
        $article->addChild(new HTMLNode('h1'))->text($post->title);
        $article->addChild(new HTMLNode('div', ['class' => 'post-meta']))
            ->text($this->get('blog/by').' '.($post->authorName ?? '').' '.$this->get('blog/in').' '.($post->categoryName ?? '').' — '.($post->createdAt ?? ''));

        // Edit button for admins
        SessionsManager::start('wf-session');

        if (SessionsManager::get('author-id') !== null) {
            $article->addChild(new HTMLNode('a', ['class' => 'btn btn-primary', 'href' => $baseUrl.'/admin/posts/'.$post->id.'/edit']))
                ->text($this->get('admin/edit'));
        }

        $article->addChild(new HTMLNode('div', ['class' => 'post-content']))->text($post->content);
        $this->insert($article);

        // Comments
        $commentRepo = new CommentRepository($db);
        $comments = $commentRepo->findByPostId($post->id);

        $section = new HTMLNode('div', ['class' => 'comments-section']);
        $section->addChild(new HTMLNode('h3'))->text($this->get('blog/comments').' ('.count($comments).')');

        foreach ($comments as $comment) {
            $div = new HTMLNode('div', ['class' => 'comment']);
            $div->addChild(new HTMLNode('span', ['class' => 'comment-author']))->text($comment->authorName);
            $div->addChild(new HTMLNode('span', ['class' => 'comment-date']))->text(' — '.($comment->createdAt ?? ''));
            $div->addChild(new HTMLNode('p'))->text($comment->content);
            $section->addChild($div);
        }

        // Comment form
        $formDiv = new HTMLNode('div', ['class' => 'comment-form']);
        $formDiv->addChild(new HTMLNode('h4'))->text($this->get('blog/leave-comment'));

        $form = new HTMLNode('form', ['id' => 'comment-form', 'data-base-url' => $baseUrl]);
        $form->addChild(new HTMLNode('input', ['type' => 'hidden', 'id' => 'postId', 'value' => (string) $post->id]));
        $form->addChild(new HTMLNode('input', ['type' => 'text', 'id' => 'authorName', 'placeholder' => $this->get('blog/name'), 'required' => '']));
        $form->addChild(new HTMLNode('input', ['type' => 'email', 'id' => 'authorEmail', 'placeholder' => $this->get('blog/email'), 'required' => '']));
        $form->addChild(new HTMLNode('textarea', ['id' => 'commentContent', 'placeholder' => $this->get('blog/comment'), 'required' => '']));
        $form->addChild(new HTMLNode('button', ['type' => 'submit']))->text($this->get('blog/submit'));

        $formDiv->addChild($form);
        $section->addChild($formDiv);
        $this->insert($section);

        $this->addJS($baseUrl.'/assets/blog-theme/js/comment.js', ['defer' => '']);

        // Sidebar categories
        $aside = $this->getChildByID('side-content-area');

        if ($aside !== null) {
            $catRepo = new CategoryRepository($db);

            foreach ($catRepo->findAll() as $cat) {
                $aside->addChild(new HTMLNode('a', ['href' => $baseUrl.'/categories/'.$cat->slug]))->text($cat->name);
            }
        }
    }
}
