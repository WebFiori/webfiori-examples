<?php
namespace App\Domain;

/**
 * Represents a customer order.
 */
class Order {
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const VALID_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    public function __construct(
        public ?int $id = null,
        public ?int $userId = null,
        public string $status = self::STATUS_PENDING,
        public float $total = 0.0,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }
}
