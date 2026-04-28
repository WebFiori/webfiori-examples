<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

/**
 * Table definition for blog categories.
 */
#[Table(name: 'categories', comment: 'Blog post categories.')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
#[Column(name: 'name', type: DataType::VARCHAR, size: 128)]
#[Column(name: 'slug', type: DataType::VARCHAR, size: 128)]
#[Column(name: 'description', type: DataType::VARCHAR, size: 500, nullable: true)]
class CategoriesTable {
}
