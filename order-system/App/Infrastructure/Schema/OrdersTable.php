<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'orders')]
class OrdersTable {
    #[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
    private int $id;
    #[Column(name: 'user_id', type: DataType::INT)]
    private int $userId;
    #[Column(name: 'status', type: DataType::VARCHAR, size: 20, default: 'pending')]
    private string $status;
    #[Column(name: 'total', type: DataType::DECIMAL, size: 10, scale: 2, default: 0)]
    private float $total;
    #[Column(name: 'created_at', type: DataType::DATETIME, nullable: true)]
    private ?string $createdAt;
    #[Column(name: 'updated_at', type: DataType::DATETIME, nullable: true)]
    private ?string $updatedAt;
}
