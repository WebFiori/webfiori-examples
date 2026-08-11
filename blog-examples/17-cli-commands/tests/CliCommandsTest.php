<?php
namespace Tests;

use App\Commands\CleanLogsCommand;
use App\Commands\GreetCommand;
use App\Commands\UsersListCommand;
use WebFiori\Cli\CommandTestCase;

class CliCommandsTest extends CommandTestCase {

    // --- GreetCommand ---

    public function testGreetWithNameAndDefaultTitle(): void {
        $output = $this->executeSingleCommand(
            new GreetCommand(),
            ['--name' => 'Alice']
        );

        $this->assertEquals(0, $this->getExitCode());
        $this->assertStringContainsString('Hello, Friend Alice!', implode('', $output));
    }

    public function testGreetWithExplicitTitle(): void {
        $output = $this->executeSingleCommand(
            new GreetCommand(),
            ['--name' => 'Ibrahim', '--title' => 'Dr']
        );

        $this->assertEquals(0, $this->getExitCode());
        $this->assertStringContainsString('Hello, Dr Ibrahim!', implode('', $output));
    }

    public function testGreetMissingNameFails(): void {
        $output = $this->executeSingleCommand(new GreetCommand(), []);

        // Missing required argument should produce non-zero exit code
        $this->assertNotEquals(0, $this->getExitCode());
    }

    // --- UsersListCommand ---

    public function testUsersListShowsAllUsers(): void {
        $output = $this->executeSingleCommand(new UsersListCommand(), []);

        $this->assertEquals(0, $this->getExitCode());
        $combined = implode('', $output);
        $this->assertStringContainsString('Alice', $combined);
        $this->assertStringContainsString('Bob', $combined);
        $this->assertStringContainsString('Charlie', $combined);
    }

    public function testUsersListFilterByRole(): void {
        $output = $this->executeSingleCommand(
            new UsersListCommand(),
            ['--role' => 'admin']
        );

        $this->assertEquals(0, $this->getExitCode());
        $combined = implode('', $output);
        $this->assertStringContainsString('Alice', $combined);
        $this->assertStringContainsString('Diana', $combined);
        $this->assertStringNotContainsString('Bob', $combined);
        $this->assertStringNotContainsString('Charlie', $combined);
    }

    public function testUsersListFilterByStatus(): void {
        $output = $this->executeSingleCommand(
            new UsersListCommand(),
            ['--status' => 'inactive']
        );

        $this->assertEquals(0, $this->getExitCode());
        $combined = implode('', $output);
        $this->assertStringContainsString('Charlie', $combined);
        $this->assertStringNotContainsString('Alice', $combined);
    }

    public function testUsersListNoMatchShowsWarning(): void {
        $output = $this->executeSingleCommand(
            new UsersListCommand(),
            ['--role' => 'editor', '--status' => 'inactive']
        );

        $this->assertEquals(0, $this->getExitCode());
        $combined = implode('', $output);
        $this->assertStringContainsString('No users found', $combined);
    }

    public function testUsersListShowsTableHeaders(): void {
        $output = $this->executeSingleCommand(new UsersListCommand(), []);

        $combined = implode('', $output);
        $this->assertStringContainsString('Name', $combined);
        $this->assertStringContainsString('Role', $combined);
        $this->assertStringContainsString('Status', $combined);
    }

    // --- CleanLogsCommand ---

    public function testCleanLogsInvalidDaysFails(): void {
        $output = $this->executeSingleCommand(
            new CleanLogsCommand(),
            ['--days' => '0']
        );

        $this->assertEquals(1, $this->getExitCode());
        $this->assertStringContainsString('must be a positive integer', implode('', $output));
    }

    public function testCleanLogsNoLogsReturnsZero(): void {
        // APP_PATH from framework bootstrap points to a real app dir;
        // if the Logs dir exists but has no old files, command succeeds with 0
        $output = $this->executeSingleCommand(
            new CleanLogsCommand(),
            ['--days' => '9999'] // threshold so far in the past nothing matches
        );

        $this->assertEquals(0, $this->getExitCode());
    }

    // --- Multi-command registration ---

    public function testMultipleCommandsCanBeRegistered(): void {
        $output = $this->executeMultiCommand(
            ['greet', '--name=Alice'],
            [],
            [new GreetCommand(), new UsersListCommand()]
        );

        $this->assertEquals(0, $this->getExitCode());
        $this->assertStringContainsString('Alice', implode('', $output));
    }
}
