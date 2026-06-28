<?php
namespace App\Database\Migrations;

use App\Infrastructure\Schema\OrderItemsTable;
use App\Infrastructure\Schema\OrdersTable;
use App\Infrastructure\Schema\PaymentsTable;
use App\Infrastructure\Schema\ProductsTable;
use App\Infrastructure\Schema\UsersTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class CreateOrderTables extends AbstractMigration {
    public function down(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        foreach ([PaymentsTable::class, OrderItemsTable::class, OrdersTable::class, ProductsTable::class, UsersTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->table($table->getNormalName())->drop()->execute();
        }
    }

    public function up(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        foreach ([UsersTable::class, ProductsTable::class, OrdersTable::class, OrderItemsTable::class, PaymentsTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->addTable($table);
            $db->table($table->getNormalName())->createTable()->execute();
        }
    }
}
