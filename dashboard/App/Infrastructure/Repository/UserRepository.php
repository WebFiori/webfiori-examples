<?php
namespace App\Infrastructure\Repository;

use App\Domain\User;
use WebFiori\Database\Repository\AbstractRepository;

class UserRepository extends AbstractRepository {
    public function findByEmail(string $email): ?User {
        $result = $this->getDatabase()->table($this->getTableName())
            ->select()->where('email', $email)->execute();

        return $result->getRowsCount() === 1 ? $this->toEntity($result->getRows()[0]) : null;
    }

    /**
     * @return User[]
     */
    public function findByRole(string $role): array {
        $result = $this->getDatabase()->table($this->getTableName())
            ->select()->where('role', $role)->execute();

        return array_map(fn ($r) => $this->toEntity($r), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'users';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'email' => $entity->email,
            'password-hash' => $entity->passwordHash,
            'role' => $entity->role,
            'is-active' => $entity->isActive ? 1 : 0,
            'language-pref' => $entity->languagePref,
            'theme-pref' => $entity->themePref,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s'),
            'updated-at' => $entity->updatedAt,
        ];
    }

    protected function toEntity(array $row): User {
        return new User(
            id: (int) $row['id'],
            name: $row['name'],
            email: $row['email'],
            passwordHash: $row['password_hash'] ?? '',
            role: $row['role'] ?? 'viewer',
            isActive: (bool) ($row['is_active'] ?? true),
            languagePref: $row['language_pref'] ?? 'EN',
            themePref: $row['theme_pref'] ?? 'light',
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null
        );
    }
}
