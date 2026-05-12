<?php
namespace App\Infrastructure\Repository;

use App\Domain\Account;
use WebFiori\Database\Repository\AbstractRepository;

class AccountRepository extends AbstractRepository {
    /** @return Account[] */
    public function findByUserId(int $userId): array {
        $result = $this->getDatabase()->table($this->getTableName())
            ->select()->where('user-id', $userId)->execute();

        return array_map(fn ($r) => $this->toEntity($r), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'accounts';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'user-id' => $entity->userId,
            'name' => $entity->name,
            'type' => $entity->type,
            'balance' => $entity->balance,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s'),
        ];
    }

    protected function toEntity(array $row): Account {
        return new Account(
            id: (int) $row['id'],
            userId: (int) ($row['user_id'] ?? 0),
            name: $row['name'],
            type: $row['type'] ?? 'checking',
            balance: (float) ($row['balance'] ?? 0),
            createdAt: $row['created_at'] ?? null
        );
    }
}
