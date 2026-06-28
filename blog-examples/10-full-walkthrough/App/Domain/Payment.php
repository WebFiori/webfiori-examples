<?php
namespace App\Domain;

/**
 * Represents a payment attempt for an order.
 */
class Payment {
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public function __construct(
        public ?int $id = null,
        public ?int $orderId = null,
        public float $amount = 0.0,
        public string $status = self::STATUS_PENDING,
        public ?string $transactionId = null,
        public ?string $createdAt = null
    ) {
    }
}
