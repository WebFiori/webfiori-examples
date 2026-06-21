<?php
namespace App\Infrastructure\Repository;

use App\Domain\Invoice;
use App\Infrastructure\Schema\InvoicesTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Repository\AbstractRepository;

class InvoiceRepository extends AbstractRepository {
    public function __construct(Database $db) {
        parent::__construct($db);
        $db->addTable(AttributeTableBuilder::build(InvoicesTable::class, $db->getConnectionInfo()->getDatabaseType()));
    }

    public function findByTenantId(int $tenantId): array {
        $result = $this->getDatabase()->table('invoices')->select()
            ->where('tenant-id', $tenantId)->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    public function findPending(): array {
        $result = $this->getDatabase()->table('invoices')->select()
            ->where('status', 'pending')->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }

    protected function getTableName(): string {
        return 'invoices';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'tenant-id' => $entity->tenantId,
            'amount' => $entity->amount,
            'status' => $entity->status,
            'period' => $entity->period,
            'created-at' => $entity->createdAt,
        ];
    }

    protected function toEntity(array $row): Invoice {
        return new Invoice(
            id: (int) $row['id'],
            tenantId: (int) $row['tenant-id'],
            amount: (float) $row['amount'],
            status: $row['status'],
            period: $row['period'] ?? null,
            createdAt: $row['created-at'] ?? null
        );
    }
}
