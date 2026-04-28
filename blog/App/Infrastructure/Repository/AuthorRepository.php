<?php
namespace App\Infrastructure\Repository;

use App\Domain\Author;
use WebFiori\Database\Repository\AbstractRepository;

/**
 * Data access layer for the `authors` table.
 */
class AuthorRepository extends AbstractRepository {
    /**
     * Finds an author by email address.
     */
    public function findByEmail(string $email): ?Author {
        $result = $this->getDatabase()
            ->table($this->getTableName())
            ->select()
            ->where('email', $email)
            ->execute();

        if ($result->getRowsCount() === 0) {
            return null;
        }

        return $this->toEntity($result->getRows()[0]);
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'authors';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'email' => $entity->email,
            'password-hash' => $entity->passwordHash,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s')
        ];
    }

    protected function toEntity(array $row): Author {
        return new Author(
            id: (int) $row['id'],
            name: $row['name'],
            email: $row['email'],
            passwordHash: $row['password_hash'] ?? '',
            createdAt: $row['created_at'] ?? null
        );
    }
}
