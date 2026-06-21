<?php
namespace App\Infrastructure\Repository;

use App\Domain\Tenant;
use App\Infrastructure\Schema\TenantsTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Repository\AbstractRepository;

class TenantRepository extends AbstractRepository {
    public function __construct(Database $db) {
        parent::__construct($db);
        $db->addTable(AttributeTableBuilder::build(TenantsTable::class, $db->getConnectionInfo()->getDatabaseType()));
    }

    public function findAll(): array {
        $result = $this->getDatabase()->table('tenants')->select()->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }

    protected function getTableName(): string {
        return 'tenants';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'plan' => $entity->plan,
            'created-at' => $entity->createdAt,
        ];
    }

    protected function toEntity(array $row): Tenant {
        return new Tenant(
            id: (int) $row['id'],
            name: $row['name'],
            plan: $row['plan'],
            createdAt: $row['created-at'] ?? null
        );
    }
}
