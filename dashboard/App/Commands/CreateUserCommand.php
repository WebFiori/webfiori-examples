<?php
namespace App\Commands;

use App\Domain\User;
use App\Infrastructure\Repository\UserRepository;
use WebFiori\Cli\ArgumentOption;
use WebFiori\Cli\Command;
use WebFiori\Database\Database;
use WebFiori\Framework\App;

class CreateUserCommand extends Command {
    public function __construct() {
        parent::__construct('users:create', [
            '--name' => [ArgumentOption::DESCRIPTION => 'User name', ArgumentOption::OPTIONAL => false],
            '--email' => [ArgumentOption::DESCRIPTION => 'User email', ArgumentOption::OPTIONAL => false],
            '--password' => [ArgumentOption::DESCRIPTION => 'User password', ArgumentOption::OPTIONAL => false],
            '--role' => [ArgumentOption::DESCRIPTION => 'Role: admin, manager, viewer', ArgumentOption::OPTIONAL => true, ArgumentOption::DEFAULT => 'viewer'],
        ], 'Create a new user.');
    }

    public function exec(): int {
        $repo = new UserRepository(new Database(App::getConfig()->getDBConnection('dashboard')));
        $user = new User(
            name: $this->getArgValue('--name'),
            email: $this->getArgValue('--email'),
            passwordHash: password_hash($this->getArgValue('--password'), PASSWORD_DEFAULT),
            role: $this->getArgValue('--role') ?? 'viewer',
            createdAt: date('Y-m-d H:i:s')
        );
        $repo->save($user);
        $this->success('User created: '.$user->email.' ('.$user->role.')');

        return 0;
    }
}
