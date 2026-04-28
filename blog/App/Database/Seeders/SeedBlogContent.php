<?php
namespace App\Database\Seeders;

use App\Database\Migrations\CreateBlogTables;
use App\Domain\Author;
use App\Domain\Category;
use App\Domain\Comment;
use App\Domain\Post;
use App\Infrastructure\Repository\AuthorRepository;
use App\Infrastructure\Repository\CategoryRepository;
use App\Infrastructure\Repository\CommentRepository;
use App\Infrastructure\Repository\PostRepository;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractSeeder;

/**
 * Seeds the blog with sample authors, categories, posts, and comments.
 */
class SeedBlogContent extends AbstractSeeder {
    public function getDependencies(): array {
        return [CreateBlogTables::class];
    }
    public function getEnvironments(): array {
        return ['dev', 'test'];
    }

    public function run(Database $db): void {
        $now = date('Y-m-d H:i:s');

        // Seed author
        $authorRepo = new AuthorRepository($db);
        $author = new Author(
            name: 'Admin',
            email: 'admin@example.com',
            passwordHash: password_hash('admin123', PASSWORD_DEFAULT),
            createdAt: $now
        );
        $authorRepo->save($author);

        // Seed categories
        $catRepo = new CategoryRepository($db);
        $categories = [
            new Category(name: 'Technology', slug: 'technology', description: 'Tech news and tutorials'),
            new Category(name: 'Lifestyle', slug: 'lifestyle', description: 'Daily life and tips'),
            new Category(name: 'Travel', slug: 'travel', description: 'Travel guides and stories'),
        ];

        foreach ($categories as $cat) {
            $catRepo->save($cat);
        }

        // Seed posts
        $postRepo = new PostRepository($db);
        $posts = [
            new Post(title: 'Getting Started with WebFiori', slug: 'getting-started-with-webfiori', content: 'WebFiori is a modern PHP framework...', authorId: 1, categoryId: 1, status: 'published', createdAt: $now),
            new Post(title: 'Building REST APIs', slug: 'building-rest-apis', content: 'Learn how to build REST APIs using WebFiori...', authorId: 1, categoryId: 1, status: 'published', createdAt: $now),
            new Post(title: 'Morning Routines', slug: 'morning-routines', content: 'Start your day right with these tips...', authorId: 1, categoryId: 2, status: 'published', createdAt: $now),
            new Post(title: 'Exploring Japan', slug: 'exploring-japan', content: 'A guide to traveling in Japan...', authorId: 1, categoryId: 3, status: 'published', createdAt: $now),
            new Post(title: 'Draft Post', slug: 'draft-post', content: 'This is a draft...', authorId: 1, categoryId: 1, status: 'draft', createdAt: $now),
        ];

        foreach ($posts as $post) {
            $postRepo->save($post);
        }

        // Seed comments on first post
        $commentRepo = new CommentRepository($db);
        $comments = [
            new Comment(postId: 1, authorName: 'Alice', authorEmail: 'alice@example.com', content: 'Great introduction!', createdAt: $now),
            new Comment(postId: 1, authorName: 'Bob', authorEmail: 'bob@example.com', content: 'Very helpful, thanks!', createdAt: $now),
        ];

        foreach ($comments as $comment) {
            $commentRepo->save($comment);
        }
    }
}
