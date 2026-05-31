<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'users')]
class UsersTable {
    #[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
    private int $id;
    #[Column(name: 'name', type: DataType::VARCHAR, size: 100)]
    private string $name;
    #[Column(name: 'email', type: DataType::VARCHAR, size: 150)]
    private string $email;
    #[Column(name: 'phone', type: DataType::VARCHAR, size: 20, nullable: true)]
    private ?string $phone;
    #[Column(name: 'password-hash', type: DataType::VARCHAR, size: 255)]
    private string $passwordHash;
    #[Column(name: 'role', type: DataType::VARCHAR, size: 20, default: 'patient')]
    private string $role;
    #[Column(name: 'active', type: DataType::BOOL, default: true)]
    private bool $active;
}
