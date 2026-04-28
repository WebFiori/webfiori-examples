<?php
namespace App\Pages\Admin;

use App\Infrastructure\Repository\CategoryRepository;
use App\Infrastructure\Repository\PostRepository;
use App\Themes\BlogTheme\BlogTheme;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Ui\HTMLNode;

/**
 * Admin page for creating or editing a blog post.
 */
class PostEditorPage extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->setTheme(BlogTheme::class);

        $db = new Database(App::getConfig()->getDBConnection('blog'));
        $categories = (new CategoryRepository($db))->findAll();

        $postId = $this->getParameterValue('id');
        $post = null;

        if ($postId !== null) {
            $post = (new PostRepository($db))->findById((int) $postId);
        }

        $isEdit = $post !== null;
        $baseUrl = $this->getTheme()->getBaseURL();
        $this->setTitle($isEdit ? $this->get('admin/edit-post') : $this->get('admin/create-post'));

        $this->insert(new HTMLNode('h1'))->text($isEdit ? $this->get('admin/edit-post') : $this->get('admin/create-post'));

        $form = new HTMLNode('form', [
            'id' => 'post-form',
            'class' => 'comment-form',
            'data-base-url' => $baseUrl,
            'data-method' => $isEdit ? 'PUT' : 'POST'
        ]);

        if ($isEdit) {
            $form->addChild(new HTMLNode('input', ['type' => 'hidden', 'id' => 'postId', 'value' => (string) $post->id]));
        }

        $form->addChild(new HTMLNode('input', ['type' => 'text', 'id' => 'title', 'placeholder' => $this->get('admin/title'), 'value' => $isEdit ? $post->title : '', 'required' => '']));
        $form->addChild(new HTMLNode('input', ['type' => 'text', 'id' => 'slug', 'placeholder' => $this->get('admin/slug'), 'value' => $isEdit ? $post->slug : '', 'required' => '']));

        // Category select
        $select = new HTMLNode('select', ['id' => 'categoryId']);
        $select->addChild(new HTMLNode('option', ['value' => '']))->text('— '.$this->get('admin/category').' —');

        foreach ($categories as $cat) {
            $attrs = ['value' => (string) $cat->id];

            if ($isEdit && $post->categoryId === $cat->id) {
                $attrs['selected'] = '';
            }
            $select->addChild(new HTMLNode('option', $attrs))->text($cat->name);
        }

        $form->addChild($select);

        // Status select
        $statusSelect = new HTMLNode('select', ['id' => 'status']);

        foreach (['draft', 'published'] as $s) {
            $attrs = ['value' => $s];

            if ($isEdit && $post->status === $s) {
                $attrs['selected'] = '';
            }
            $statusSelect->addChild(new HTMLNode('option', $attrs))->text(ucfirst($s));
        }

        $form->addChild($statusSelect);

        $textarea = new HTMLNode('textarea', ['id' => 'content', 'placeholder' => $this->get('admin/content'), 'style' => 'height:200px']);

        if ($isEdit) {
            $textarea->text($post->content);
        }
        $form->addChild($textarea);

        $form->addChild(new HTMLNode('button', ['type' => 'submit']))->text($this->get('admin/save'));

        $this->insert($form);
        $this->addJS($baseUrl.'/assets/blog-theme/js/post-editor.js', ['defer' => '']);
    }
}
