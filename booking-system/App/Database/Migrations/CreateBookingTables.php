<?php
namespace App\Database\Migrations;

use App\Infrastructure\Schema\AppointmentsTable;
use App\Infrastructure\Schema\ServicesTable;
use App\Infrastructure\Schema\UsersTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class CreateBookingTables extends AbstractMigration {
    public function down(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        foreach ([AppointmentsTable::class, ServicesTable::class, UsersTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->table($table->getNormalName())->drop()->execute();
        }
    }

    public function up(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        foreach ([UsersTable::class, ServicesTable::class, AppointmentsTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->addTable($table);
            $db->table($table->getNormalName())->createTable()->execute();
        }
    }
}
