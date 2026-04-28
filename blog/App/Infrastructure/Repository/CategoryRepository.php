<?php
namespace App\Infrastructure\Repository;

use App\Domain\Category;
use WebFiori\Database\Repository\AbstractRepository;

/**
 * Data access layer for the `categories` table.
 */
class CategoryRepository extends AbstractRepository {
    /**
     * Finds a category by its URL slug.
     */
    public function findBySlug(string $slug): ?Category {
        $result = $this->getDatabase()
            ->table($this->getTableName())
            ->select()
            ->where('slug', $slug)
            ->execute();

        if ($result->getRowsCount() === 0) {
            return null;
        }

        return $this->toEntity($result->getRows()[0]);
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
            'name' => $entity->name,
            'slug' => $entity->slug,
            'description' => $entity->description
        ];
    }

    protected function toEntity(array $row): Category {
        return new Category(
            id: (int) $row['id'],
            name: $row['name'],
            slug: $row['slug'],
            description: $row['description'] ?? ''
        );
    }
}
