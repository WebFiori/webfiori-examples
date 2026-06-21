<?php
namespace App\Domain;

class Appointment {
    public const STATUS_BOOKED = 'booked';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    public function __construct(
        public ?int $id = null,
        public ?int $patientId = null,
        public ?int $providerId = null,
        public ?int $serviceId = null,
        public ?string $startTime = null,
        public ?string $endTime = null,
        public string $status = self::STATUS_BOOKED,
        public ?string $notes = null,
        public bool $reminderSent = false,
        public ?string $createdAt = null
    ) {
    }
}
