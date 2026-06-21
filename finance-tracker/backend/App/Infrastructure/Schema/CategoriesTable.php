<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'categories')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'user_id', type: DataType::INT, nullable: true)]
#[Column(name: 'name', type: DataType::VARCHAR, size: 64)]
#[Column(name: 'type', type: DataType::VARCHAR, size: 10, default: 'expense')]
#[Column(name: 'icon', type: DataType::VARCHAR, size: 32, nullable: true)]
#[Column(name: 'color', type: DataType::VARCHAR, size: 10, default: '#333333')]
class CategoriesTable {
}
