<?php
namespace App\Domain;

/**
 * Domain entity representing a comment on a blog post.
 */
class Comment {
    public function __construct(
        public ?int $id = null,
        public ?int $postId = null,
        public string $authorName = '',
        public string $authorEmail = '',
        public string $content = '',
        public ?string $createdAt = null
    ) {
    }
}
