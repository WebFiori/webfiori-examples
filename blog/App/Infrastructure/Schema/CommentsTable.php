<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

/**
 * Table definition for post comments.
 */
#[Table(name: 'comments', comment: 'Comments on blog posts.')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'post_id', type: DataType::INT)]
#[Column(name: 'author_name', type: DataType::VARCHAR, size: 128)]
#[Column(name: 'author_email', type: DataType::VARCHAR, size: 256)]
#[Column(name: 'content', type: DataType::VARCHAR, size: 2000)]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
class CommentsTable {
}
