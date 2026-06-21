<?php
namespace App\Database\Migrations;

use App\Infrastructure\Schema\TasksTable;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

/**
 * Migration that adds `priority` and `due_date` columns to the `tasks` table.
 *
 * Checks if columns already exist before adding or removing them,
 * making this migration safe to run on both fresh and existing databases.
 *
 * Depends on {@see CreateTasksTable} so the table exists before altering.
 */
class AddPriorityAndDueDate extends AbstractMigration {
    public function getDependencies(): array {
        return [CreateTasksTable::class];
    }

    public function up(Database $db): void {
        $table = $db->addTableFromClass(TasksTable::class);
        $name = $table->getNormalName();

        if (!$this->columnExists($db, 'priority')) {
            $db->table($name)->addCol('priority')->execute();
        }

        if (!$this->columnExists($db, 'due_date')) {
            $db->table($name)->addCol('due-date')->execute();
        }
    }

    public function down(Database $db): void {
        $table = $db->addTableFromClass(TasksTable::class);
        $name = $table->getNormalName();

        if ($this->columnExists($db, 'priority')) {
            $db->table($name)->dropCol('priority')->execute();
        }

        if ($this->columnExists($db, 'due_date')) {
            $db->table($name)->dropCol('due-date')->execute();
        }
    }

    /**
     * Checks if a column exists in the tasks table.
     */
    private function columnExists(Database $db, string $column): bool {
        $dbName = $db->getConnectionInfo()->getDBName();
        $result = $db->raw(
            "SELECT 1 AS col_exists FROM INFORMATION_SCHEMA.COLUMNS "
            . "WHERE TABLE_CATALOG = '$dbName' AND TABLE_NAME = 'tasks' AND COLUMN_NAME = '$column'"
        )->execute()->fetchAll();

        return !empty($result);
    }
}
