<?php
namespace App\Domain;

class Invoice {
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';

    public function __construct(
        public ?int $id = null,
        public ?int $tenantId = null,
        public float $amount = 0.0,
        public string $status = self::STATUS_PENDING,
        public ?string $period = null,
        public ?string $createdAt = null
    ) {
    }
}
