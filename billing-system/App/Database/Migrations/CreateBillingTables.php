<?php
namespace App\Database\Migrations;

use App\Infrastructure\Schema\InvoicesTable;
use App\Infrastructure\Schema\SubscriptionsTable;
use App\Infrastructure\Schema\TenantsTable;
use App\Infrastructure\Schema\UsersTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class CreateBillingTables extends AbstractMigration {
    public function down(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        foreach ([InvoicesTable::class, SubscriptionsTable::class, UsersTable::class, TenantsTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->table($table->getNormalName())->drop()->execute();
        }
    }

    public function up(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        foreach ([TenantsTable::class, UsersTable::class, SubscriptionsTable::class, InvoicesTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->addTable($table);
            $db->table($table->getNormalName())->createTable()->execute();
        }
    }
}
