<?php
namespace App\Domain;

class Project {
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $description = '',
        public string $status = 'active',
        public ?int $ownerId = null,
        public ?string $ownerName = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }
}
