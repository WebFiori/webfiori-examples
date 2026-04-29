<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'users', comment: 'Application users with roles.')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'name', type: DataType::VARCHAR, size: 128)]
#[Column(name: 'email', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'password_hash', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'role', type: DataType::VARCHAR, size: 20, default: 'viewer')]
#[Column(name: 'is_active', type: DataType::INT, default: 1)]
#[Column(name: 'language_pref', type: DataType::VARCHAR, size: 5, default: 'EN')]
#[Column(name: 'theme_pref', type: DataType::VARCHAR, size: 10, default: 'light')]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
#[Column(name: 'updated_at', type: DataType::DATETIME, nullable: true)]
class UsersTable {
}
