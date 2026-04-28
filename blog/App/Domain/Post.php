<?php
namespace App\Domain;

/**
 * Domain entity representing a blog post.
 */
class Post {
    public function __construct(
        public ?int $id = null,
        public string $title = '',
        public string $slug = '',
        public string $content = '',
        public ?int $authorId = null,
        public ?int $categoryId = null,
        public string $status = 'draft',
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $authorName = null,
        public ?string $categoryName = null
    ) {
    }
}
