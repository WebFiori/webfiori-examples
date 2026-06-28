<?php
namespace App\Events;

class UserRegisteredEvent {
    public function __construct(
        public readonly int $userId,
        public readonly string $email,
        public readonly string $name
    ) {}
}
