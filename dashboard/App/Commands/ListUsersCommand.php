<?php
namespace App\Commands;

use App\Infrastructure\Repository\UserRepository;
use WebFiori\Cli\Command;
use WebFiori\Database\Database;
use WebFiori\Framework\App;

class ListUsersCommand extends Command {
    public function __construct() {
        parent::__construct('users:list', [], 'List all users.');
    }

    public function exec(): int {
        $users = (new UserRepository(new Database(App::getConfig()->getDBConnection('dashboard'))))->findAll();
        $rows = [];

        foreach ($users as $u) {
            $rows[] = [$u->id, $u->name, $u->email, $u->role, $u->isActive ? 'Yes' : 'No'];
        }

        $this->table($rows, ['ID', 'Name', 'Email', 'Role', 'Active']);

        return 0;
    }
}
