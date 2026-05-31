<?php
namespace App\Infrastructure\Repository;

use App\Domain\Subscription;
use App\Infrastructure\Schema\SubscriptionsTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Repository\AbstractRepository;

class SubscriptionRepository extends AbstractRepository {
    public function __construct(Database $db) {
        parent::__construct($db);
        $db->addTable(AttributeTableBuilder::build(SubscriptionsTable::class, $db->getConnectionInfo()->getDatabaseType()));
    }

    public function findByTenantId(int $tenantId): ?Subscription {
        $result = $this->getDatabase()->table('subscriptions')->select()
            ->where('tenant-id', $tenantId)->execute();
        $rows = $result->fetchAll();

        return !empty($rows) ? $this->toEntity(end($rows)) : null;
    }

    public function findExpired(): array {
        $now = date('Y-m-d H:i:s');
        $result = $this->getDatabase()->raw(
            "SELECT * FROM subscriptions WHERE status = 'active' AND [expires-at] < ?",
            [$now]
        )->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }

    protected function getTableName(): string {
        return 'subscriptions';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'tenant-id' => $entity->tenantId,
            'plan' => $entity->plan,
            'status' => $entity->status,
            'starts-at' => $entity->startsAt,
            'expires-at' => $entity->expiresAt,
        ];
    }

    protected function toEntity(array $row): Subscription {
        return new Subscription(
            id: (int) $row['id'],
            tenantId: (int) ($row['tenant-id'] ?? $row['tenant_id'] ?? 0),
            plan: $row['plan'],
            status: $row['status'],
            startsAt: $row['starts-at'] ?? $row['starts_at'] ?? null,
            expiresAt: $row['expires-at'] ?? $row['expires_at'] ?? null
        );
    }
}
