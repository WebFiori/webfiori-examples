<?php
namespace App\Infrastructure\Repository;

use App\Domain\Project;
use WebFiori\Database\Repository\AbstractRepository;

class ProjectRepository extends AbstractRepository {
    /**
     * @return Project[]
     */
    public function findAllWithOwner(): array {
        $sql = 'SELECT p.*, u.name AS owner_name FROM projects p LEFT JOIN users u ON p.owner_id = u.id ORDER BY p.created_at DESC';

        return array_map(fn ($r) => $this->toEntity($r), $this->getDatabase()->raw($sql)->execute()->fetchAll());
    }

    public function findByIdWithOwner(int $id): ?Project {
        $sql = 'SELECT p.*, u.name AS owner_name FROM projects p LEFT JOIN users u ON p.owner_id = u.id WHERE p.id = ?';
        $result = $this->getDatabase()->raw($sql, [$id])->execute();

        return $result->getRowsCount() === 1 ? $this->toEntity($result->getRows()[0]) : null;
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'projects';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'description' => $entity->description,
            'status' => $entity->status,
            'owner-id' => $entity->ownerId,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s'),
            'updated-at' => $entity->updatedAt,
        ];
    }

    protected function toEntity(array $row): Project {
        return new Project(
            id: (int) $row['id'],
            name: $row['name'],
            description: $row['description'] ?? '',
            status: $row['status'] ?? 'active',
            ownerId: isset($row['owner_id']) ? (int) $row['owner_id'] : null,
            ownerName: $row['owner_name'] ?? null,
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null
        );
    }
}
