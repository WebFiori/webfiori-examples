<?php
namespace App\Infrastructure\Repository;

use App\Domain\Reply;
use WebFiori\Database\Repository\AbstractRepository;

/**
 * Data access layer for the `replies` table.
 */
class ReplyRepository extends AbstractRepository {
    /**
     * @return Reply[]
     */
    public function findByTicketId(int $ticketId): array {
        $result = $this->getDatabase()
            ->table($this->getTableName())
            ->select()
            ->where('ticket-id', $ticketId)
            ->execute();

        return array_map(fn ($row) => $this->toEntity($row), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'replies';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'ticket-id' => $entity->ticketId,
            'author-name' => $entity->authorName,
            'content' => $entity->content,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s'),
        ];
    }

    protected function toEntity(array $row): Reply {
        return new Reply(
            id: (int) $row['id'],
            ticketId: isset($row['ticket_id']) ? (int) $row['ticket_id'] : null,
            authorName: $row['author_name'] ?? '',
            content: $row['content'] ?? '',
            createdAt: $row['created_at'] ?? null
        );
    }
}
