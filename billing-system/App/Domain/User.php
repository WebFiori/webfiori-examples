<?php
namespace App\Domain;

use WebFiori\Http\SecurityPrincipal;

class User implements SecurityPrincipal {
    public function __construct(
        public ?int $id = null,
        public ?int $tenantId = null,
        public string $name = '',
        public string $email = '',
        public string $passwordHash = '',
        public string $role = 'member',
        public bool $active = true
    ) {
    }

    public function getAuthorities(): array {
        return match ($this->role) {
            'super-admin' => ['tenants.manage', 'subscriptions.manage', 'invoices.view', 'invoices.generate', 'usage.view'],
            'tenant-admin' => ['subscriptions.view', 'invoices.view', 'usage.view', 'members.manage'],
            'member' => ['invoices.view', 'usage.view'],
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
