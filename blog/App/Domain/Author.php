<?php
namespace App\Domain;

/**
 * Domain entity representing a blog author (admin user).
 */
class Author {
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $email = '',
        public string $passwordHash = '',
        public ?string $createdAt = null
    ) {
    }
}
