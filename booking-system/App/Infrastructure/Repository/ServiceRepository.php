<?php
namespace App\Infrastructure\Repository;

use App\Domain\Service;
use App\Infrastructure\Schema\ServicesTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Repository\AbstractRepository;

class ServiceRepository extends AbstractRepository {
    public function __construct(Database $db) {
        parent::__construct($db);
        $db->addTable(AttributeTableBuilder::build(ServicesTable::class, $db->getConnectionInfo()->getDatabaseType()));
    }

    public function findAll(): array {
        $result = $this->getDatabase()->table('services')->select()->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }

    protected function getTableName(): string {
        return 'services';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'duration-minutes' => $entity->durationMinutes,
            'price' => $entity->price,
        ];
    }

    protected function toEntity(array $row): Service {
        return new Service(
            id: (int) $row['id'],
            name: $row['name'],
            durationMinutes: (int) $row['duration-minutes'],
            price: (float) $row['price']
        );
    }
}
