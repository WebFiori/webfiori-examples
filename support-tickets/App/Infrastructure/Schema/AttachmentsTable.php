<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'attachments', comment: 'File attachments on tickets.')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'ticket_id', type: DataType::INT)]
#[Column(name: 'file_name', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'file_path', type: DataType::VARCHAR, size: 512)]
#[Column(name: 'mime_type', type: DataType::VARCHAR, size: 128)]
#[Column(name: 'file_size', type: DataType::INT)]
#[Column(name: 'uploaded_at', type: DataType::DATETIME, default: 'now()')]
class AttachmentsTable {
}
