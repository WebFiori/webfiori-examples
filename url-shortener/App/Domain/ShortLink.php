<?php
namespace App\Domain;

/**
 * Domain entity representing a shortened URL.
 */
class ShortLink {
    public function __construct(
        public ?string $id = null,
        public string $redirectTo = '',
        public string $ipAddress = '',
        public int $numberOfClicks = 0,
        public ?string $createdAt = null,
        public ?string $expiresAt = null
    ) {
    }
}
