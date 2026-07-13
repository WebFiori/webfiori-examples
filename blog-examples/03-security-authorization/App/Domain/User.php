<?php
namespace App\Domain;

use WebFiori\Http\SecurityPrincipal;

/**
 * User entity implementing SecurityPrincipal for framework integration.
 */
class User implements SecurityPrincipal {
    public function __construct(
        public int $id,
        public string $name,
        public string $role
    ) {
    }

    public function getAuthorities(): array {
        return match ($this->role) {
            'admin' => ['orders.create', 'orders.view', 'orders.cancel', 'orders.manage'],
            'customer' => ['orders.create', 'orders.view', 'orders.cancel'],
            default => []
        };
    }

    public function getId(): int|string {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getRoles(): array {
        return [$this->role];
    }

    public function isActive(): bool {
        return true;
    }
}
