<?php
namespace App\Infrastructure\Repository;

use App\Domain\AuditLogEntry;
use WebFiori\Database\Repository\AbstractRepository;

class AuditLogRepository extends AbstractRepository {
    /**
     * @return AuditLogEntry[]
     */
    public function findFiltered(?int $userId = null, ?string $action = null, ?string $fromDate = null, ?string $toDate = null): array {
        $sql = 'SELECT a.*, u.name AS user_name FROM audit_log a LEFT JOIN users u ON a.user_id = u.id WHERE 1=1';
        $params = [];

        if ($userId !== null) {
            $sql .= ' AND a.user_id = ?';
            $params[] = $userId;
        }

        if ($action !== null) {
            $sql .= ' AND a.action = ?';
            $params[] = $action;
        }

        if ($fromDate !== null) {
            $sql .= ' AND a.created_at >= ?';
            $params[] = $fromDate;
        }

        if ($toDate !== null) {
            $sql .= ' AND a.created_at <= ?';
            $params[] = $toDate;
        }

        $sql .= ' ORDER BY a.created_at DESC';

        return array_map(fn ($r) => $this->toEntity($r), $this->getDatabase()->raw($sql, $params)->execute()->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'audit_log';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'user-id' => $entity->userId,
            'action' => $entity->action,
            'entity-type' => $entity->entityType,
            'entity-id' => $entity->entityId,
            'details' => $entity->details,
            'ip-address' => $entity->ipAddress,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s'),
        ];
    }

    protected function toEntity(array $row): AuditLogEntry {
        return new AuditLogEntry(
            id: (int) $row['id'],
            userId: isset($row['user_id']) ? (int) $row['user_id'] : null,
            userName: $row['user_name'] ?? null,
            action: $row['action'] ?? '',
            entityType: $row['entity_type'] ?? '',
            entityId: isset($row['entity_id']) ? (int) $row['entity_id'] : null,
            details: $row['details'] ?? '',
            ipAddress: $row['ip_address'] ?? '',
            createdAt: $row['created_at'] ?? null
        );
    }
}
