<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'payments')]
class PaymentsTable {
    #[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
    private int $id;
    #[Column(name: 'order_id', type: DataType::INT)]
    private int $orderId;
    #[Column(name: 'amount', type: DataType::DECIMAL, size: 10, scale: 2)]
    private float $amount;
    #[Column(name: 'status', type: DataType::VARCHAR, size: 20, default: 'pending')]
    private string $status;
    #[Column(name: 'transaction_id', type: DataType::VARCHAR, size: 100, nullable: true)]
    private ?string $transactionId;
    #[Column(name: 'created_at', type: DataType::DATETIME, nullable: true)]
    private ?string $createdAt;
}
