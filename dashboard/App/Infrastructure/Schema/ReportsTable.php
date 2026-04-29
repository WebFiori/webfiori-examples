<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'reports', comment: 'Generated reports.')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'title', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'generated_by', type: DataType::INT)]
#[Column(name: 'file_path', type: DataType::VARCHAR, size: 512)]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
class ReportsTable {
}
