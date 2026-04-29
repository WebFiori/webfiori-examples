<?php
namespace App\Infrastructure\Repository;

use App\Domain\Attachment;
use WebFiori\Database\Repository\AbstractRepository;

/**
 * Data access layer for the `attachments` table.
 */
class AttachmentRepository extends AbstractRepository {
    /**
     * @return Attachment[]
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
        return 'attachments';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'ticket-id' => $entity->ticketId,
            'file-name' => $entity->fileName,
            'file-path' => $entity->filePath,
            'mime-type' => $entity->mimeType,
            'file-size' => $entity->fileSize,
            'uploaded-at' => $entity->uploadedAt ?? date('Y-m-d H:i:s'),
        ];
    }

    protected function toEntity(array $row): Attachment {
        return new Attachment(
            id: (int) $row['id'],
            ticketId: isset($row['ticket_id']) ? (int) $row['ticket_id'] : null,
            fileName: $row['file_name'] ?? '',
            filePath: $row['file_path'] ?? '',
            mimeType: $row['mime_type'] ?? '',
            fileSize: (int) ($row['file_size'] ?? 0),
            uploadedAt: $row['uploaded_at'] ?? null
        );
    }
}
