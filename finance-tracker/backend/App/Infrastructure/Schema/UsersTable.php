<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'users')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'name', type: DataType::VARCHAR, size: 128)]
#[Column(name: 'email', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'password_hash', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'currency_pref', type: DataType::VARCHAR, size: 10, default: 'USD')]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
class UsersTable {
}
