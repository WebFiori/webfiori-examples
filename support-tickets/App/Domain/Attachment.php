<?php
namespace App\Domain;

/**
 * Domain entity representing a file attachment on a ticket.
 */
class Attachment {
    public function __construct(
        public ?int $id = null,
        public ?int $ticketId = null,
        public string $fileName = '',
        public string $filePath = '',
        public string $mimeType = '',
        public int $fileSize = 0,
        public ?string $uploadedAt = null
    ) {
    }
}
