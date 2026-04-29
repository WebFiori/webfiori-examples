<?php
namespace App\Domain;

/**
 * Domain entity representing a support ticket.
 */
class Ticket {
    public function __construct(
        public ?int $id = null,
        public string $subject = '',
        public string $description = '',
        public string $submitterName = '',
        public string $submitterEmail = '',
        public string $status = 'open',
        public string $priority = 'medium',
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }
}
