<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

/**
 * Table definition for blog posts.
 *
 * Foreign keys to `authors` and `categories` are added manually in the
 * migration since the attribute-based builder handles single-table definitions.
 */
#[Table(name: 'posts', comment: 'Blog posts.')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'title', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'slug', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'content', type: DataType::TEXT, nullable: true)]
#[Column(name: 'author_id', type: DataType::INT)]
#[Column(name: 'category_id', type: DataType::INT, nullable: true)]
#[Column(name: 'status', type: DataType::VARCHAR, size: 20, default: 'draft')]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
#[Column(name: 'updated_at', type: DataType::DATETIME, nullable: true)]
class PostsTable {
}
