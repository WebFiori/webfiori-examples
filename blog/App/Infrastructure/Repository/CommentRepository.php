<?php
namespace App\Infrastructure\Repository;

use App\Domain\Comment;
use WebFiori\Database\Repository\AbstractRepository;

/**
 * Data access layer for the `comments` table.
 */
class CommentRepository extends AbstractRepository {
    /**
     * Finds all comments for a given post, ordered by creation date.
     *
     * @return Comment[]
     */
    public function findByPostId(int $postId): array {
        $result = $this->getDatabase()
            ->table($this->getTableName())
            ->select()
            ->where('post-id', $postId)
            ->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'comments';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'post-id' => $entity->postId,
            'author-name' => $entity->authorName,
            'author-email' => $entity->authorEmail,
            'content' => $entity->content,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s')
        ];
    }

    protected function toEntity(array $row): Comment {
        return new Comment(
            id: (int) $row['id'],
            postId: isset($row['post_id']) ? (int) $row['post_id'] : null,
            authorName: $row['author_name'] ?? '',
            authorEmail: $row['author_email'] ?? '',
            content: $row['content'] ?? '',
            createdAt: $row['created_at'] ?? null
        );
    }
}
