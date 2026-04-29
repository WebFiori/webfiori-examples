<?php
namespace App\Database\Migrations;

use App\Infrastructure\Schema\AttachmentsTable;
use App\Infrastructure\Schema\RepliesTable;
use App\Infrastructure\Schema\TicketsTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

/**
 * Creates all support ticket tables in dependency order.
 */
class CreateTicketTables extends AbstractMigration {
    public function down(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        foreach ([AttachmentsTable::class, RepliesTable::class, TicketsTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->table($table->getNormalName())->drop()->execute();
        }
    }
    public function up(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        foreach ([TicketsTable::class, RepliesTable::class, AttachmentsTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->addTable($table);
            $db->table($table->getNormalName())->createTable()->execute();
        }
    }
}
