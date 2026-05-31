<?php
namespace App\Domain;

use WebFiori\Http\SecurityPrincipal;

class User implements SecurityPrincipal {
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $email = '',
        public string $phone = '',
        public string $passwordHash = '',
        public string $role = 'patient',
        public bool $active = true
    ) {
    }

    public function getAuthorities(): array {
        return match ($this->role) {
            'admin' => ['appointments.view', 'appointments.create', 'appointments.cancel', 'appointments.manage', 'providers.manage', 'services.manage'],
            'provider' => ['appointments.view', 'appointments.cancel', 'appointments.manage'],
            'patient' => ['appointments.view', 'appointments.create', 'appointments.cancel'],
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
