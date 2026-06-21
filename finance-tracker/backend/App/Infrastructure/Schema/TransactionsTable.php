<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'transactions')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'account_id', type: DataType::INT)]
#[Column(name: 'category_id', type: DataType::INT, nullable: true)]
#[Column(name: 'type', type: DataType::VARCHAR, size: 10, default: 'expense')]
#[Column(name: 'amount', type: DataType::DECIMAL, size: 12)]
#[Column(name: 'description', type: DataType::VARCHAR, size: 256, nullable: true)]
#[Column(name: 'date', type: DataType::DATETIME)]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
class TransactionsTable {
}
