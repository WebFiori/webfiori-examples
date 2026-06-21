<?php
namespace App\Infrastructure\Repository;

use App\Domain\Product;
use WebFiori\Database\Repository\AbstractRepository;

class ProductRepository extends AbstractRepository {
    public function __construct(\WebFiori\Database\Database $db) {
        parent::__construct($db);
        $table = \WebFiori\Database\Attributes\AttributeTableBuilder::build(
            \App\Infrastructure\Schema\ProductsTable::class,
            $db->getConnectionInfo()->getDatabaseType()
        );
        $db->addTable($table);
    }

    public function findAll(): array {
        $result = $this->getDatabase()->table('products')->select()->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    public function decrementStock(int $productId, int $quantity): void {
        $dbType = $this->getDatabase()->getConnectionInfo()->getDatabaseType();

        if ($dbType === 'mssql') {
            $this->getDatabase()->raw(
                "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?",
                [$quantity, $productId, $quantity]
            )->execute();
        } else {
            $this->getDatabase()->raw(
                "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?",
                [$quantity, $productId, $quantity]
            )->execute();
        }
    }

    protected function getIdField(): string {
        return 'id';
    }

    protected function getTableName(): string {
        return 'products';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'description' => $entity->description,
            'price' => $entity->price,
            'stock' => $entity->stock,
            'created-at' => $entity->createdAt,
        ];
    }

    protected function toEntity(array $row): Product {
        return new Product(
            id: (int) $row['id'],
            name: $row['name'],
            description: $row['description'] ?? '',
            price: (float) $row['price'],
            stock: (int) $row['stock'],
            createdAt: $row['created-at'] ?? null
        );
    }
}
