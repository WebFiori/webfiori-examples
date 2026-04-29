<?php
namespace App\Domain;

/**
 * Domain entity representing a reply to a support ticket.
 */
class Reply {
    public function __construct(
        public ?int $id = null,
        public ?int $ticketId = null,
        public string $authorName = '',
        public string $content = '',
        public ?string $createdAt = null
    ) {
    }
}
