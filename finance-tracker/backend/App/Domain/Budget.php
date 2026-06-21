<?php
namespace App\Domain;

class Budget {
    public function __construct(
        public ?int $id = null,
        public ?int $userId = null,
        public ?int $categoryId = null,
        public float $amountLimit = 0,
        public string $period = 'monthly',
        public ?string $startDate = null,
        public ?string $categoryName = null,
        public float $spent = 0
    ) {
    }
}
