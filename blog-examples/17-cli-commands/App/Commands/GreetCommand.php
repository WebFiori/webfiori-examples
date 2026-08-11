<?php
namespace App\Commands;

use WebFiori\Cli\Command;
use WebFiori\Cli\ArgumentOption as Option;

/**
 * A command that greets a user by name.
 * Demonstrates basic arguments with optional/required, defaults, and allowed values.
 *
 * Usage:
 *   php webfiori greet --name="Alice"
 *   php webfiori greet --name="Bob" --title="Dr"
 */
class GreetCommand extends Command {
    public function __construct() {
        parent::__construct('greet', [
            '--name' => [
                Option::DESCRIPTION => 'Name of the person to greet.',
                Option::OPTIONAL    => false,
            ],
            '--title' => [
                Option::DESCRIPTION => 'Honorific title.',
                Option::OPTIONAL    => true,
                Option::DEFAULT     => 'Friend',
                Option::VALUES      => ['Mr', 'Ms', 'Dr', 'Prof', 'Friend'],
            ],
        ], 'Greet a person by name.');
    }

    public function exec(): int {
        $name  = $this->getArgValue('--name');
        $title = $this->getArgValue('--title') ?? 'Friend';

        $this->println("Hello, %s %s!", $title, $name);

        return 0;
    }
}
