<?php
namespace App\Database\Seeders;

use App\Database\Migrations\CreateTasksTable;
use App\Domain\Task;
use App\Infrastructure\Repository\TaskRepository;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractSeeder;

/**
 * Seeds the `tasks` table with sample data for development and testing.
 *
 * Only runs in `dev` and `test` environments. Depends on
 * {@see CreateTasksTable} so the table exists before seeding.
 */
class SeedSampleTasks extends AbstractSeeder {
    /**
     * Restricts this seeder to non-production environments.
     *
     * @return string[]
     */
    public function getEnvironments(): array {
        return ['dev', 'test'];
    }

    /**
     * Ensures the tasks table is created before seeding.
     *
     * @return string[]
     */
    public function getDependencies(): array {
        return [CreateTasksTable::class];
    }

    /**
     * Inserts five sample tasks with a mix of statuses.
     */
    public function run(Database $db): void {
        $repo = new TaskRepository($db);
        $tasks = [
            new Task(title: 'Buy groceries', description: 'Milk, eggs, bread, and butter', createdAt: date('Y-m-d H:i:s')),
            new Task(title: 'Write documentation', description: 'Update the API docs for v2', createdAt: date('Y-m-d H:i:s')),
            new Task(title: 'Fix login bug', description: 'Users cannot login with email', status: 'completed', createdAt: date('Y-m-d H:i:s')),
            new Task(title: 'Deploy to staging', description: '', createdAt: date('Y-m-d H:i:s')),
            new Task(title: 'Review pull request', description: 'PR #42 needs code review', status: 'completed', createdAt: date('Y-m-d H:i:s')),
        ];

        foreach ($tasks as $task) {
            $repo->save($task);
        }
    }
}
