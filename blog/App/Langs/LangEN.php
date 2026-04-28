<?php
namespace App\Langs;

use WebFiori\Framework\Lang;

/**
 * English language variables for the blog.
 */
class LangEN extends Lang {
    public function __construct() {
        parent::__construct('ltr', 'EN');

        $this->createAndSet('nav', [
            'home' => 'Home',
            'login' => 'Login',
            'logout' => 'Logout',
            'admin' => 'Admin',
            'categories' => 'Categories',
        ]);
        $this->createAndSet('blog', [
            'title' => 'WebFiori Blog',
            'no-posts' => 'No posts found.',
            'read-more' => 'Read More',
            'by' => 'By',
            'in' => 'in',
            'comments' => 'Comments',
            'leave-comment' => 'Leave a Comment',
            'name' => 'Name',
            'email' => 'Email',
            'comment' => 'Comment',
            'submit' => 'Submit',
            'published' => 'Published',
            'draft' => 'Draft',
            'page' => 'Page',
            'of' => 'of',
        ]);
        $this->createAndSet('admin', [
            'dashboard' => 'Dashboard',
            'manage-posts' => 'Manage Posts',
            'manage-categories' => 'Manage Categories',
            'create-post' => 'Create Post',
            'edit-post' => 'Edit Post',
            'title' => 'Title',
            'slug' => 'Slug',
            'content' => 'Content',
            'category' => 'Category',
            'status' => 'Status',
            'actions' => 'Actions',
            'save' => 'Save',
            'delete' => 'Delete',
            'edit' => 'Edit',
        ]);
        $this->createAndSet('auth', [
            'login-title' => 'Admin Login',
            'email' => 'Email',
            'password' => 'Password',
            'login-btn' => 'Login',
            'invalid-credentials' => 'Invalid email or password.',
        ]);
    }
}
