<?php
namespace App\Domain;

class Report {
    public function __construct(
        public ?int $id = null,
        public string $title = '',
        public ?int $generatedBy = null,
        public ?string $generatedByName = null,
        public string $filePath = '',
        public ?string $createdAt = null
    ) {
    }
}
