<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'invoices')]
class InvoicesTable {
    #[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
    private int $id;
    #[Column(name: 'tenant-id', type: DataType::INT)]
    private int $tenantId;
    #[Column(name: 'amount', type: DataType::DECIMAL, size: 10, scale: 2)]
    private float $amount;
    #[Column(name: 'status', type: DataType::VARCHAR, size: 20, default: 'pending')]
    private string $status;
    #[Column(name: 'period', type: DataType::VARCHAR, size: 20, nullable: true)]
    private ?string $period;
    #[Column(name: 'created-at', type: DataType::DATETIME, nullable: true)]
    private ?string $createdAt;
}
