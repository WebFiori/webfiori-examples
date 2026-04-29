<?php
namespace App\Database\Migrations;

use App\Infrastructure\Schema\AuditLogTable;
use App\Infrastructure\Schema\ProjectsTable;
use App\Infrastructure\Schema\ReportsTable;
use App\Infrastructure\Schema\UsersTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class CreateDashboardTables extends AbstractMigration {
    public function down(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        foreach ([AuditLogTable::class, ReportsTable::class, ProjectsTable::class, UsersTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->table($table->getNormalName())->drop()->execute();
        }
    }
    public function up(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        foreach ([UsersTable::class, ProjectsTable::class, ReportsTable::class, AuditLogTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->addTable($table);
            $db->table($table->getNormalName())->createTable()->execute();
        }
    }
}
