<?php
namespace App\Domain;

class Transaction {
    public function __construct(
        public ?int $id = null,
        public ?int $accountId = null,
        public ?int $categoryId = null,
        public string $type = 'expense',
        public float $amount = 0,
        public string $description = '',
        public ?string $date = null,
        public ?string $createdAt = null,
        public ?string $accountName = null,
        public ?string $categoryName = null
    ) {
    }
}
