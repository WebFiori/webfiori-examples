<?php
namespace App\Domain;

class User {
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $email = '',
        public string $passwordHash = '',
        public string $role = 'viewer',
        public bool $isActive = true,
        public string $languagePref = 'EN',
        public string $themePref = 'light',
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }
}
