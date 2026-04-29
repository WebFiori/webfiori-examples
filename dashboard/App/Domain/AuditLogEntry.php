<?php
namespace App\Domain;

class AuditLogEntry {
    public function __construct(
        public ?int $id = null,
        public ?int $userId = null,
        public ?string $userName = null,
        public string $action = '',
        public string $entityType = '',
        public ?int $entityId = null,
        public string $details = '',
        public string $ipAddress = '',
        public ?string $createdAt = null
    ) {
    }
}
