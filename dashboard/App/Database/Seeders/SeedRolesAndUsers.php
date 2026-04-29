<?php
namespace App\Database\Seeders;

use App\Database\Migrations\CreateDashboardTables;
use App\Domain\User;
use App\Infrastructure\Repository\UserRepository;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractSeeder;

class SeedRolesAndUsers extends AbstractSeeder {
    public function getDependencies(): array {
        return [CreateDashboardTables::class];
    }
    public function getEnvironments(): array {
        return ['dev', 'test'];
    }

    public function run(Database $db): void {
        $repo = new UserRepository($db);
        $now = date('Y-m-d H:i:s');

        $users = [
            new User(name: 'Admin User', email: 'admin@example.com', passwordHash: password_hash('admin123', PASSWORD_DEFAULT), role: 'admin', createdAt: $now),
            new User(name: 'Manager User', email: 'manager@example.com', passwordHash: password_hash('manager123', PASSWORD_DEFAULT), role: 'manager', createdAt: $now),
            new User(name: 'Viewer User', email: 'viewer@example.com', passwordHash: password_hash('viewer123', PASSWORD_DEFAULT), role: 'viewer', createdAt: $now),
        ];

        foreach ($users as $u) {
            $repo->save($u);
        }
    }
}
