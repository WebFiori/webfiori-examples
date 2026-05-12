<?php
namespace App\Domain;

class Account {
    public function __construct(
        public ?int $id = null,
        public ?int $userId = null,
        public string $name = '',
        public string $type = 'checking',
        public float $balance = 0,
        public ?string $createdAt = null
    ) {
    }
}
