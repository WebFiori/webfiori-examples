<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

/**
 * Table definition for blog authors (admin users).
 */
#[Table(name: 'authors', comment: 'Blog authors / admin users.')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'name', type: DataType::VARCHAR, size: 128)]
#[Column(name: 'email', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'password_hash', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
class AuthorsTable {
}
