<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'products')]
class ProductsTable {
    #[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
    private int $id;
    #[Column(name: 'name', type: DataType::VARCHAR, size: 200)]
    private string $name;
    #[Column(name: 'description', type: DataType::TEXT, nullable: true)]
    private ?string $description;
    #[Column(name: 'price', type: DataType::DECIMAL, size: 10, scale: 2)]
    private float $price;
    #[Column(name: 'stock', type: DataType::INT, default: 0)]
    private int $stock;
    #[Column(name: 'created_at', type: DataType::DATETIME, nullable: true)]
    private ?string $createdAt;
}
