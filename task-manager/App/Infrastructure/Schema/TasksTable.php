<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

/**
 * Database table definition for the `tasks` table.
 *
 * Uses PHP 8 attributes to declare columns in a database-agnostic way.
 * The `autoIncrement` flag applies to MySQL while `identity` applies to
 * MSSQL, so both are set on the primary key for cross-database support.
 *
 * This class is consumed by {@see \WebFiori\Database\Attributes\AttributeTableBuilder}
 * at migration time to generate the appropriate DDL for the target database.
 */
#[Table(name: 'tasks', comment: 'Stores task items.')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'title', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'description', type: DataType::VARCHAR, size: 2000, nullable: true)]
#[Column(name: 'status', type: DataType::VARCHAR, size: 20, default: 'pending')]
#[Column(name: 'priority', type: DataType::VARCHAR, size: 10, default: 'medium')]
#[Column(name: 'due_date', type: DataType::DATETIME, nullable: true)]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
#[Column(name: 'updated_at', type: DataType::DATETIME, nullable: true)]
class TasksTable {
}
