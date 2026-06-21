<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'appointments')]
class AppointmentsTable {
    #[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
    private int $id;
    #[Column(name: 'patient-id', type: DataType::INT)]
    private int $patientId;
    #[Column(name: 'provider-id', type: DataType::INT)]
    private int $providerId;
    #[Column(name: 'service-id', type: DataType::INT)]
    private int $serviceId;
    #[Column(name: 'start-time', type: DataType::DATETIME)]
    private string $startTime;
    #[Column(name: 'end-time', type: DataType::DATETIME)]
    private string $endTime;
    #[Column(name: 'status', type: DataType::VARCHAR, size: 20, default: 'booked')]
    private string $status;
    #[Column(name: 'notes', type: DataType::TEXT, nullable: true)]
    private ?string $notes;
    #[Column(name: 'reminder-sent', type: DataType::BOOL, default: false)]
    private bool $reminderSent;
    #[Column(name: 'created-at', type: DataType::DATETIME, nullable: true)]
    private ?string $createdAt;
}
