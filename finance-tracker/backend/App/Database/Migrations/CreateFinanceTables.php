<?php
namespace App\Database\Migrations;

use App\Infrastructure\Schema\AccountsTable;
use App\Infrastructure\Schema\BudgetsTable;
use App\Infrastructure\Schema\CategoriesTable;
use App\Infrastructure\Schema\TransactionsTable;
use App\Infrastructure\Schema\UsersTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class CreateFinanceTables extends AbstractMigration {
    public function down(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        foreach ([BudgetsTable::class, TransactionsTable::class, CategoriesTable::class, AccountsTable::class, UsersTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->table($table->getNormalName())->drop()->execute();
        }
    }
    public function up(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        foreach ([UsersTable::class, AccountsTable::class, CategoriesTable::class, TransactionsTable::class, BudgetsTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->addTable($table);
            $db->table($table->getNormalName())->createTable()->execute();
        }
    }
}
