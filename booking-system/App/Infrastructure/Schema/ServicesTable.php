<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'services')]
class ServicesTable {
    #[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
    private int $id;
    #[Column(name: 'name', type: DataType::VARCHAR, size: 100)]
    private string $name;
    #[Column(name: 'duration-minutes', type: DataType::INT, default: 30)]
    private int $durationMinutes;
    #[Column(name: 'price', type: DataType::DECIMAL, size: 10, scale: 2)]
    private float $price;
}
