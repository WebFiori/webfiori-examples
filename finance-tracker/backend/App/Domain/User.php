<?php
namespace App\Domain;

class User {
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $email = '',
        public string $passwordHash = '',
        public string $currencyPref = 'USD',
        public ?string $createdAt = null
    ) {
    }
}
