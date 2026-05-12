<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'accounts')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'user_id', type: DataType::INT)]
#[Column(name: 'name', type: DataType::VARCHAR, size: 128)]
#[Column(name: 'type', type: DataType::VARCHAR, size: 20, default: 'checking')]
#[Column(name: 'balance', type: DataType::DECIMAL, size: 12)]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
class AccountsTable {
}
