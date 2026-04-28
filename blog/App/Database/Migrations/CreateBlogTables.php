<?php
namespace App\Database\Migrations;

use App\Infrastructure\Schema\AuthorsTable;
use App\Infrastructure\Schema\CategoriesTable;
use App\Infrastructure\Schema\CommentsTable;
use App\Infrastructure\Schema\PostsTable;
use WebFiori\Database\Attributes\AttributeTableBuilder;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

/**
 * Creates all blog tables: authors, categories, posts, comments.
 *
 * Tables are created in dependency order so that foreign key references
 * are valid. The `down()` method drops them in reverse order.
 */
class CreateBlogTables extends AbstractMigration {
    public function down(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        // Drop in reverse dependency order
        foreach ([CommentsTable::class, PostsTable::class, CategoriesTable::class, AuthorsTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->table($table->getNormalName())->drop()->execute();
        }
    }
    public function up(Database $db): void {
        $dbType = $db->getConnectionInfo()->getDatabaseType();

        // Create tables in dependency order
        foreach ([AuthorsTable::class, CategoriesTable::class, PostsTable::class, CommentsTable::class] as $cls) {
            $table = AttributeTableBuilder::build($cls, $dbType);
            $db->addTable($table);
            $db->table($table->getNormalName())->createTable()->execute();
        }
    }
}
