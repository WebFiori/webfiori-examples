<?php
namespace App\Infrastructure\Repository;

use App\Domain\User;
use WebFiori\Database\Repository\AbstractRepository;

class UserRepository extends AbstractRepository {
    public function __construct(\WebFiori\Database\Database $db) {
        parent::__construct($db);
        $table = \WebFiori\Database\Attributes\AttributeTableBuilder::build(
            \App\Infrastructure\Schema\UsersTable::class,
            $db->getConnectionInfo()->getDatabaseType()
        );
        $db->addTable($table);
    }

    public function findByEmail(string $email): ?User {
        $result = $this->getDatabase()->table('users')->select()
            ->where('email', $email)->execute();
        $rows = $result->fetchAll();

        return !empty($rows) ? $this->toEntity($rows[0]) : null;
    }

    protected function getIdField(): string {
        return 'id';
    }

    protected function getTableName(): string {
        return 'users';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'name' => $entity->name,
            'email' => $entity->email,
            'password-hash' => $entity->passwordHash,
            'role' => $entity->role,
            'active' => $entity->active,
        ];
    }

    protected function toEntity(array $row): User {
        return new User(
            id: (int) $row['id'],
            name: $row['name'],
            email: $row['email'],
            passwordHash: $row['password_hash'] ?? '',
            role: $row['role'] ?? 'customer',
            active: (bool) ($row['active'] ?? true)
        );
    }
}
