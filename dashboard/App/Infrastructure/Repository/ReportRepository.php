<?php
namespace App\Infrastructure\Repository;

use App\Domain\Report;
use WebFiori\Database\Repository\AbstractRepository;

class ReportRepository extends AbstractRepository {
    /**
     * @return Report[]
     */
    public function findAllWithUser(): array {
        $sql = 'SELECT r.*, u.name AS generated_by_name FROM reports r LEFT JOIN users u ON r.generated_by = u.id ORDER BY r.created_at DESC';

        return array_map(fn ($r) => $this->toEntity($r), $this->getDatabase()->raw($sql)->execute()->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'reports';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'title' => $entity->title,
            'generated-by' => $entity->generatedBy,
            'file-path' => $entity->filePath,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s'),
        ];
    }

    protected function toEntity(array $row): Report {
        return new Report(
            id: (int) $row['id'],
            title: $row['title'],
            generatedBy: isset($row['generated_by']) ? (int) $row['generated_by'] : null,
            generatedByName: $row['generated_by_name'] ?? null,
            filePath: $row['file_path'] ?? '',
            createdAt: $row['created_at'] ?? null
        );
    }
}
