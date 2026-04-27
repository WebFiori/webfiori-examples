<?php
namespace App\Database\Migrations;

use App\Infrastructure\Schema\TasksTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

/**
 * Migration that creates the `tasks` table.
 *
 * Uses {@see AttributeTableBuilder} to build the table definition from
 * the PHP 8 attributes declared on {@see TasksTable}. The builder
 * automatically generates the correct DDL for the target database type
 * (MySQL or MSSQL).
 */
class CreateTasksTable extends AbstractMigration {
    /**
     * Creates the `tasks` table.
     */
    public function up(Database $db): void {
        $table = AttributeTableBuilder::build(TasksTable::class, $db->getConnectionInfo()->getDatabaseType());
        $db->addTable($table);
        $db->table($table->getNormalName())->createTable()->execute();
    }

    /**
     * Drops the `tasks` table.
     */
    public function down(Database $db): void {
        $table = AttributeTableBuilder::build(TasksTable::class, $db->getConnectionInfo()->getDatabaseType());
        $db->table($table->getNormalName())->drop()->execute();
    }
}
