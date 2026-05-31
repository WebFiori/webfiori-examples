<?php
namespace App\Domain;

class Tenant {
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $plan = 'free',
        public ?string $createdAt = null
    ) {
    }
}
