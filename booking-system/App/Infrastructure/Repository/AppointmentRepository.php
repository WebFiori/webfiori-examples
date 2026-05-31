<?php
namespace App\Infrastructure\Repository;

use App\Domain\Appointment;
use App\Infrastructure\Schema\AppointmentsTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Repository\AbstractRepository;

class AppointmentRepository extends AbstractRepository {
    public function __construct(Database $db) {
        parent::__construct($db);
        $db->addTable(AttributeTableBuilder::build(AppointmentsTable::class, $db->getConnectionInfo()->getDatabaseType()));
    }

    public function findByPatientId(int $patientId): array {
        $result = $this->getDatabase()->table('appointments')->select()
            ->where('patient-id', $patientId)->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    public function findByProviderId(int $providerId): array {
        $result = $this->getDatabase()->table('appointments')->select()
            ->where('provider-id', $providerId)->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    public function findUpcomingUnreminded(): array {
        $now = date('Y-m-d H:i:s');
        $tomorrow = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $result = $this->getDatabase()->raw(
            "SELECT * FROM appointments WHERE status = 'booked' AND [reminder-sent] = 0 AND [start-time] BETWEEN ? AND ?",
            [$now, $tomorrow]
        )->execute();

        return array_map(fn($row) => $this->toEntityFromRaw($row), $result->fetchAll());
    }

    public function findPastUncompleted(): array {
        $now = date('Y-m-d H:i:s');
        $result = $this->getDatabase()->raw(
            "SELECT * FROM appointments WHERE status = 'booked' AND [end-time] < ?",
            [$now]
        )->execute();

        return array_map(fn($row) => $this->toEntityFromRaw($row), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }

    protected function getTableName(): string {
        return 'appointments';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'patient-id' => $entity->patientId,
            'provider-id' => $entity->providerId,
            'service-id' => $entity->serviceId,
            'start-time' => $entity->startTime,
            'end-time' => $entity->endTime,
            'status' => $entity->status,
            'notes' => $entity->notes,
            'reminder-sent' => $entity->reminderSent,
            'created-at' => $entity->createdAt,
        ];
    }

    protected function toEntity(array $row): Appointment {
        return new Appointment(
            id: (int) $row['id'],
            patientId: (int) $row['patient-id'],
            providerId: (int) $row['provider-id'],
            serviceId: (int) $row['service-id'],
            startTime: $row['start-time'],
            endTime: $row['end-time'],
            status: $row['status'],
            notes: $row['notes'] ?? null,
            reminderSent: (bool) ($row['reminder-sent'] ?? false),
            createdAt: $row['created-at'] ?? null
        );
    }

    private function toEntityFromRaw(array $row): Appointment {
        return new Appointment(
            id: (int) $row['id'],
            patientId: (int) $row['patient-id'],
            providerId: (int) $row['provider-id'],
            serviceId: (int) $row['service-id'],
            startTime: $row['start-time'],
            endTime: $row['end-time'],
            status: $row['status'],
            notes: $row['notes'] ?? null,
            reminderSent: (bool) ($row['reminder-sent'] ?? false),
            createdAt: $row['created-at'] ?? null
        );
    }
}
