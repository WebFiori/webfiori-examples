<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'tenants')]
class TenantsTable {
    #[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
    private int $id;
    #[Column(name: 'name', type: DataType::VARCHAR, size: 100)]
    private string $name;
    #[Column(name: 'plan', type: DataType::VARCHAR, size: 20, default: 'free')]
    private string $plan;
    #[Column(name: 'created-at', type: DataType::DATETIME, nullable: true)]
    private ?string $createdAt;
}
