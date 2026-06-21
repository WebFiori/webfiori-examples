<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'budgets')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'user_id', type: DataType::INT)]
#[Column(name: 'category_id', type: DataType::INT)]
#[Column(name: 'amount_limit', type: DataType::DECIMAL, size: 12)]
#[Column(name: 'period', type: DataType::VARCHAR, size: 10, default: 'monthly')]
#[Column(name: 'start_date', type: DataType::DATETIME)]
class BudgetsTable {
}
