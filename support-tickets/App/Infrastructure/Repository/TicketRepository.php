<?php
namespace App\Infrastructure\Repository;

use App\Domain\Ticket;
use WebFiori\Database\Repository\AbstractRepository;

/**
 * Data access layer for the `tickets` table.
 */
class TicketRepository extends AbstractRepository {
    /**
     * Finds tickets filtered by status and/or submitter email.
     *
     * @return Ticket[]
     */
    public function findFiltered(?string $status = null, ?string $email = null): array {
        $query = $this->getDatabase()->table($this->getTableName())->select();

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($email !== null) {
            if ($status !== null) {
                $query->andWhere('submitter-email', $email);
            } else {
                $query->where('submitter-email', $email);
            }
        }

        return array_map(fn ($row) => $this->toEntity($row), $query->execute()->fetchAll());
    }

    /**
     * Finds all open tickets grouped by priority for the daily digest.
     *
     * @return array<string, Ticket[]>
     */
    public function findOpenGroupedByPriority(): array {
        $result = $this->getDatabase()
            ->table($this->getTableName())
            ->select()
            ->where('status', 'closed', '!=')
            ->execute();

        $grouped = ['high' => [], 'medium' => [], 'low' => []];

        foreach ($result->fetchAll() as $row) {
            $ticket = $this->toEntity($row);
            $grouped[$ticket->priority][] = $ticket;
        }

        return $grouped;
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'tickets';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'subject' => $entity->subject,
            'description' => $entity->description,
            'submitter-name' => $entity->submitterName,
            'submitter-email' => $entity->submitterEmail,
            'status' => $entity->status,
            'priority' => $entity->priority,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s'),
            'updated-at' => $entity->updatedAt,
        ];
    }

    protected function toEntity(array $row): Ticket {
        return new Ticket(
            id: (int) $row['id'],
            subject: $row['subject'],
            description: $row['description'] ?? '',
            submitterName: $row['submitter_name'] ?? '',
            submitterEmail: $row['submitter_email'] ?? '',
            status: $row['status'] ?? 'open',
            priority: $row['priority'] ?? 'medium',
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null
        );
    }
}
