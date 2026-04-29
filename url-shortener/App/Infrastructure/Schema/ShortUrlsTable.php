<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'short_urls', comment: 'Shortened URLs.')]
#[Column(name: 'id', type: DataType::VARCHAR, size: 6, primary: true)]
#[Column(name: 'redirect_to', type: DataType::VARCHAR, size: 4000)]
#[Column(name: 'ip_address', type: DataType::VARCHAR, size: 128)]
#[Column(name: 'number_of_clicks', type: DataType::INT, default: 0)]
#[Column(name: 'created_at', type: DataType::DATETIME, default: 'now()')]
#[Column(name: 'expires_at', type: DataType::DATETIME, nullable: true)]
class ShortUrlsTable {
}
