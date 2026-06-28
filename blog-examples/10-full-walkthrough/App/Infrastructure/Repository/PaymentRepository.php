<?php
namespace App\Infrastructure\Repository;

use App\Domain\Payment;
use WebFiori\Database\Repository\AbstractRepository;

class PaymentRepository extends AbstractRepository {
    public function __construct(\WebFiori\Database\Database $db) {
        parent::__construct($db);
        $table = \WebFiori\Database\Attributes\AttributeTableBuilder::build(
            \App\Infrastructure\Schema\PaymentsTable::class,
            $db->getConnectionInfo()->getDatabaseType()
        );
        $db->addTable($table);
    }

    public function findByOrderId(int $orderId): array {
        $result = $this->getDatabase()->table('payments')->select()
            ->where('order-id', $orderId)->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }

    protected function getTableName(): string {
        return 'payments';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'order-id' => $entity->orderId,
            'amount' => $entity->amount,
            'status' => $entity->status,
            'transaction-id' => $entity->transactionId,
            'created-at' => $entity->createdAt,
        ];
    }

    protected function toEntity(array $row): Payment {
        return new Payment(
            id: (int) $row['id'],
            orderId: (int) $row['order-id'],
            amount: (float) $row['amount'],
            status: $row['status'],
            transactionId: $row['transaction-id'] ?? null,
            createdAt: $row['created-at'] ?? null
        );
    }
}
