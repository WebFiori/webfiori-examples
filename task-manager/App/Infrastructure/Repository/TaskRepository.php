<?php
namespace App\Infrastructure\Repository;

use App\Domain\Task;
use WebFiori\Database\Repository\AbstractRepository;

/**
 * Data access layer for the `tasks` table.
 *
 * Extends {@see AbstractRepository} which provides built-in CRUD operations
 * (`findById`, `findAll`, `save`, `deleteById`, `paginate`, etc.).
 * Custom query methods are added here for application-specific needs.
 */
class TaskRepository extends AbstractRepository {
    /**
     * {@inheritDoc}
     */
    protected function getTableName(): string {
        return 'tasks';
    }

    /**
     * {@inheritDoc}
     */
    protected function getIdField(): string {
        return 'id';
    }

    /**
     * Maps a database row to a Task entity.
     *
     * @param array $row Associative array from the database result set.
     *
     * @return Task
     */
    protected function toEntity(array $row): Task {
        return new Task(
            id: (int) $row['id'],
            title: $row['title'],
            description: $row['description'] ?? '',
            status: $row['status'] ?? 'pending',
            priority: $row['priority'] ?? 'medium',
            dueDate: $row['due_date'] ?? null,
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null
        );
    }

    /**
     * Converts a Task entity to an associative array for database operations.
     *
     * Column keys use kebab-case to match the WebFiori database layer convention
     * (e.g. `created-at` maps to the `created_at` column).
     *
     * @param object $entity The Task entity.
     *
     * @return array<string, mixed>
     */
    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'title' => $entity->title,
            'description' => $entity->description,
            'status' => $entity->status,
            'priority' => $entity->priority,
            'due-date' => $entity->dueDate,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s'),
            'updated-at' => $entity->updatedAt
        ];
    }

    /**
     * Finds all tasks that match the given status.
     *
     * @param string $status The status to filter by (e.g. 'pending', 'completed').
     *
     * @return Task[] Array of matching Task entities.
     */
    public function findByStatus(string $status): array {
        $result = $this->getDatabase()
            ->table($this->getTableName())
            ->select()
            ->where('status', $status)
            ->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    /**
     * Finds the most recently inserted task that matches the given title and creation time.
     *
     * Used after an insert to retrieve the task with its auto-generated ID,
     * since the database layer does not expose a last-insert-ID method.
     *
     * @param string $title     The title of the task.
     * @param string $createdAt The creation timestamp used during insert.
     *
     * @return Task|null The matching task, or null if not found.
     */
    public function findLastByTitle(string $title, string $createdAt): ?Task {
        $result = $this->getDatabase()
            ->table($this->getTableName())
            ->select()
            ->where('title', $title)
            ->andWhere('created-at', $createdAt)
            ->execute();

        $rows = $result->fetchAll();

        if (empty($rows)) {
            return null;
        }

        return $this->toEntity(end($rows));
    }
}
