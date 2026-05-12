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
            'currency-pref' => $entity->currencyPref,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s'),
        ];
    }

    protected function toEntity(array $row): User {
        return new User(
            id: (int) $row['id'],
            name: $row['name'],
            email: $row['email'],
            passwordHash: $row['password_hash'] ?? '',
            currencyPref: $row['currency_pref'] ?? 'USD',
            createdAt: $row['created_at'] ?? null
        );
    }
}
