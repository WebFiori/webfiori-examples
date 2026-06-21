<?php
namespace App\Database\Migrations;

use App\Infrastructure\Schema\ProductsTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class CreateProductsTable extends AbstractMigration {

    public function up(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();
        $table = AttributeTableBuilder::build(ProductsTable::class, $dbType);
        $db->addTable($table);
        $db->table('products')->createTable()->execute();
    }

    public function down(Database $db): void {
        $db->table('products')->drop()->execute();
    }
}
