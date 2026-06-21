<?php
namespace App\Infrastructure\Repository;

use App\Domain\User;
use App\Infrastructure\Schema\UsersTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Repository\AbstractRepository;

class UserRepository extends AbstractRepository {
    public function __construct(Database $db) {
        parent::__construct($db);
        $db->addTable(AttributeTableBuilder::build(UsersTable::class, $db->getConnectionInfo()->getDatabaseType()));
    }

    public function findByEmail(string $email): ?User {
        $result = $this->getDatabase()->table('users')->select()->where('email', $email)->execute();
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
            'tenant-id' => $entity->tenantId,
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
            tenantId: (int) $row['tenant-id'],
            name: $row['name'],
            email: $row['email'],
            passwordHash: $row['password-hash'],
            role: $row['role'] ?? 'member',
            active: (bool) ($row['active'] ?? true)
        );
    }
}
