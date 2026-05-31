<?php
namespace App\Domain;

use WebFiori\Http\SecurityPrincipal;

/**
 * Represents an authenticated user in the system.
 */
class User implements SecurityPrincipal {
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $email = '',
        public string $passwordHash = '',
        public string $role = 'customer',
        public bool $active = true
    ) {
    }

    public function getAuthorities(): array {
        return match ($this->role) {
            'admin' => ['orders.create', 'orders.view', 'orders.cancel', 'orders.update', 'orders.ship', 'orders.manage', 'products.manage'],
            'staff' => ['orders.view', 'orders.update', 'orders.ship'],
            'customer' => ['orders.create', 'orders.view', 'orders.cancel'],
            default => []
        };
    }

    public function getId(): int|string {
        return $this->id ?? 0;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getRoles(): array {
        return [$this->role];
    }

    public function isActive(): bool {
        return $this->active;
    }
}
