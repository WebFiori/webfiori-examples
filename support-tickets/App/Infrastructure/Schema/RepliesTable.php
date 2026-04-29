<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'replies', comment: 'Replies to support tickets.')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'ticket_id', type: DataType::INT)]
#[Column(name: 'author_name', type: DataType::VARCHAR, size: 128)]
#[Column(name: 'content', type: DataType::TEXT)]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
class RepliesTable {
}
