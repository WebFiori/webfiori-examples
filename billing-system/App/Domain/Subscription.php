<?php
namespace App\Domain;

class Subscription {
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public function __construct(
        public ?int $id = null,
        public ?int $tenantId = null,
        public string $plan = 'free',
        public string $status = self::STATUS_ACTIVE,
        public ?string $startsAt = null,
        public ?string $expiresAt = null
    ) {
    }
}
