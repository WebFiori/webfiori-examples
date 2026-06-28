<?php
namespace App\Infrastructure\Repository;

use App\Domain\Order;
use WebFiori\Database\Repository\AbstractRepository;

class OrderRepository extends AbstractRepository {
    public function __construct(\WebFiori\Database\Database $db) {
        parent::__construct($db);
        $table = \WebFiori\Database\Attributes\AttributeTableBuilder::build(
            \App\Infrastructure\Schema\OrdersTable::class,
            $db->getConnectionInfo()->getDatabaseType()
        );
        $db->addTable($table);
    }

    public function findByUserId(int $userId): array {
        $result = $this->getDatabase()->table('orders')->select()
            ->where('user-id', $userId)
            ->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    public function findExpiredPending(int $hoursOld = 24): array {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hoursOld} hours"));
        $result = $this->getDatabase()->raw(
            "SELECT * FROM orders WHERE status = 'pending' AND [created-at] < ?",
            [$cutoff]
        )->execute();

        return array_map(fn($row) => $this->toEntityFromRaw($row), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }

    protected function getTableName(): string {
        return 'orders';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'user-id' => $entity->userId,
            'status' => $entity->status,
            'total' => $entity->total,
            'created-at' => $entity->createdAt,
            'updated-at' => $entity->updatedAt,
        ];
    }

    protected function toEntity(array $row): Order {
        return new Order(
            id: (int) $row['id'],
            userId: (int) $row['user-id'],
            status: $row['status'],
            total: (float) $row['total'],
            createdAt: $row['created-at'] ?? null,
            updatedAt: $row['updated-at'] ?? null
        );
    }

    private function toEntityFromRaw(array $row): Order {
        return new Order(
            id: (int) $row['id'],
            userId: (int) $row['user-id'],
            status: $row['status'],
            total: (float) $row['total'],
            createdAt: $row['created-at'] ?? null,
            updatedAt: $row['updated-at'] ?? null
        );
    }
}
