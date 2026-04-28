<?php
namespace App\Domain;

/**
 * Domain entity representing a blog category.
 */
class Category {
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $slug = '',
        public string $description = ''
    ) {
    }
}
