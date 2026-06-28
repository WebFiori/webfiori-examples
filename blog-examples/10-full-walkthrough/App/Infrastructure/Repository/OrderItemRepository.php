<?php
namespace App\Infrastructure\Repository;

use App\Domain\OrderItem;
use WebFiori\Database\Repository\AbstractRepository;

class OrderItemRepository extends AbstractRepository {
    public function __construct(\WebFiori\Database\Database $db) {
        parent::__construct($db);
        $table = \WebFiori\Database\Attributes\AttributeTableBuilder::build(
            \App\Infrastructure\Schema\OrderItemsTable::class,
            $db->getConnectionInfo()->getDatabaseType()
        );
        $db->addTable($table);
    }

    public function findByOrderId(int $orderId): array {
        $result = $this->getDatabase()->table('order_items')->select()
            ->where('order-id', $orderId)->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }

    protected function getTableName(): string {
        return 'order_items';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'order-id' => $entity->orderId,
            'product-id' => $entity->productId,
            'quantity' => $entity->quantity,
            'unit-price' => $entity->unitPrice,
        ];
    }

    protected function toEntity(array $row): OrderItem {
        return new OrderItem(
            id: (int) $row['id'],
            orderId: (int) $row['order_id'],
            productId: (int) $row['product_id'],
            quantity: (int) $row['quantity'],
            unitPrice: (float) $row['unit_price']
        );
    }
}
