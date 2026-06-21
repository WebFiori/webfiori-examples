<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'subscriptions')]
class SubscriptionsTable {
    #[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
    private int $id;
    #[Column(name: 'tenant-id', type: DataType::INT)]
    private int $tenantId;
    #[Column(name: 'plan', type: DataType::VARCHAR, size: 20)]
    private string $plan;
    #[Column(name: 'status', type: DataType::VARCHAR, size: 20, default: 'active')]
    private string $status;
    #[Column(name: 'starts-at', type: DataType::DATETIME, nullable: true)]
    private ?string $startsAt;
    #[Column(name: 'expires-at', type: DataType::DATETIME, nullable: true)]
    private ?string $expiresAt;
}
