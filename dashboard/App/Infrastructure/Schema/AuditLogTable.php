<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'audit_log', comment: 'Audit trail for write operations.')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'user_id', type: DataType::INT, nullable: true)]
#[Column(name: 'action', type: DataType::VARCHAR, size: 50)]
#[Column(name: 'entity_type', type: DataType::VARCHAR, size: 50)]
#[Column(name: 'entity_id', type: DataType::INT, nullable: true)]
#[Column(name: 'details', type: DataType::VARCHAR, size: 4000, nullable: true)]
#[Column(name: 'ip_address', type: DataType::VARCHAR, size: 128)]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
class AuditLogTable {
}
