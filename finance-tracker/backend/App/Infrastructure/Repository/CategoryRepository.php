<?php
namespace App\Infrastructure\Repository;

use App\Domain\Category;
use WebFiori\Database\Repository\AbstractRepository;

class CategoryRepository extends AbstractRepository {
    /** @return Category[] */
    public function findByUserId(int $userId): array {
        // Return global categories (user_id IS NULL) + user's own
        $sql = 'SELECT * FROM categories WHERE user_id IS NULL OR user_id = ? ORDER BY name';

        return array_map(fn ($r) => $this->toEntity($r), $this->getDatabase()->raw($sql, [$userId])->execute()->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'categories';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'user-id' => $entity->userId,
            'name' => $entity->name,
            'type' => $entity->type,
            'icon' => $entity->icon,
            'color' => $entity->color,
        ];
    }

    protected function toEntity(array $row): Category {
        return new Category(
            id: (int) $row['id'],
            userId: isset($row['user_id']) ? (int) $row['user_id'] : null,
            name: $row['name'],
            type: $row['type'] ?? 'expense',
            icon: $row['icon'] ?? '',
            color: $row['color'] ?? '#333333'
        );
    }
}
