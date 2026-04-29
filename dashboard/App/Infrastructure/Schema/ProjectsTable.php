<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'projects', comment: 'Projects managed in the dashboard.')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'name', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'description', type: DataType::TEXT)]
#[Column(name: 'status', type: DataType::VARCHAR, size: 20, default: 'active')]
#[Column(name: 'owner_id', type: DataType::INT)]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
#[Column(name: 'updated_at', type: DataType::DATETIME, nullable: true)]
class ProjectsTable {
}
