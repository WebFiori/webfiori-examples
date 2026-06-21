<?php

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

use App\Database\Migrations\CreateProductsTable;
use App\Database\Seeders\SeedProducts;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Database;

$dbFile = $root . '/App/Storage/app.db';

// Fresh database for each test run
if (file_exists($dbFile)) {
    unlink($dbFile);
}

$connInfo = new ConnectionInfo('sqlite', '', '', $dbFile);
$db = new Database($connInfo);

// Run migration
$migration = new CreateProductsTable();
$migration->execute($db);

// Run seeder
$seeder = new SeedProducts();
$seeder->execute($db);

fwrite(STDOUT, "Database migrated and seeded.\n");
