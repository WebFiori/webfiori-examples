<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'tickets', comment: 'Support tickets.')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'subject', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'description', type: DataType::TEXT)]
#[Column(name: 'submitter_name', type: DataType::VARCHAR, size: 128)]
#[Column(name: 'submitter_email', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'status', type: DataType::VARCHAR, size: 20, default: 'open')]
#[Column(name: 'priority', type: DataType::VARCHAR, size: 10, default: 'medium')]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
#[Column(name: 'updated_at', type: DataType::DATETIME, nullable: true)]
class TicketsTable {
}
