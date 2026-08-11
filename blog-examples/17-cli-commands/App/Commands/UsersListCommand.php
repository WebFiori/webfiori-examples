<?php
namespace App\Commands;

use WebFiori\Cli\Command;
use WebFiori\Cli\ArgumentOption as Option;

/**
 * Lists users in a formatted table.
 * Demonstrates table output and optional filtering.
 *
 * Usage:
 *   php webfiori users:list
 *   php webfiori users:list --role=admin
 */
class UsersListCommand extends Command {
    private array $users = [
        ['id' => 1, 'name' => 'Alice',   'role' => 'admin',  'status' => 'active'],
        ['id' => 2, 'name' => 'Bob',     'role' => 'user',   'status' => 'active'],
        ['id' => 3, 'name' => 'Charlie', 'role' => 'user',   'status' => 'inactive'],
        ['id' => 4, 'name' => 'Diana',   'role' => 'admin',  'status' => 'active'],
        ['id' => 5, 'name' => 'Eve',     'role' => 'editor', 'status' => 'active'],
    ];

    public function __construct() {
        parent::__construct('users:list', [
            '--role' => [
                Option::DESCRIPTION => 'Filter by role (admin, user, editor).',
                Option::OPTIONAL    => true,
                Option::VALUES      => ['admin', 'user', 'editor'],
            ],
            '--status' => [
                Option::DESCRIPTION => 'Filter by status (active, inactive).',
                Option::OPTIONAL    => true,
                Option::VALUES      => ['active', 'inactive'],
            ],
        ], 'List application users.');
    }

    public function exec(): int {
        $roleFilter   = $this->getArgValue('--role');
        $statusFilter = $this->getArgValue('--status');

        $users = $this->users;

        if ($roleFilter !== null) {
            $users = array_values(array_filter($users, fn($u) => $u['role'] === $roleFilter));
        }

        if ($statusFilter !== null) {
            $users = array_values(array_filter($users, fn($u) => $u['status'] === $statusFilter));
        }

        if (empty($users)) {
            $this->warning('No users found matching the given filters.');

            return 0;
        }

        $rows = array_map(fn($u) => [$u['id'], $u['name'], $u['role'], $u['status']], $users);
        $this->table($rows, ['ID', 'Name', 'Role', 'Status']);

        $this->info(count($users) . ' user(s) found.');

        return 0;
    }
}
