<?php
namespace App\Domain;

class Service {
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public int $durationMinutes = 30,
        public float $price = 0.0
    ) {
    }
}
