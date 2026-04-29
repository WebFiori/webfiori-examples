<?php
namespace App\Database\Migrations;

use App\Infrastructure\Schema\ShortUrlsTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class CreateShortUrlsTable extends AbstractMigration {
    public function down(Database $db): void {
        $table = AttributeTableBuilder::build(ShortUrlsTable::class, $db->getConnectionInfo()->getDatabaseType());
        $db->table($table->getNormalName())->drop()->execute();
    }
    public function up(Database $db): void {
        $table = AttributeTableBuilder::build(ShortUrlsTable::class, $db->getConnectionInfo()->getDatabaseType());
        $db->addTable($table);
        $db->table($table->getNormalName())->createTable()->execute();
    }
}
