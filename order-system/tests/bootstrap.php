<?php
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

use WebFiori\Framework\App;

fwrite(STDOUT, "Initializing App...\n");

try {
    App::initiate('App', 'public', $root . '/public');
    App::start();
} catch (Throwable $e) {
    fwrite(STDOUT, "Error During Initialization: " . $e->getMessage() . "\n");
    exit(1);
}

fwrite(STDOUT, "Done\n");
fwrite(STDOUT, "----------------------------------------------\n");

$connection = App::getConfig()->getDBConnection('orders');

if ($connection === null) {
    fwrite(STDERR, "\nERROR: Database connection 'orders' is not configured.\n\n");
    fwrite(STDERR, "Add it using:\n\n");
    fwrite(STDERR, "  php webfiori add:db-connection --db-type=mssql --host=localhost --port=1433 \\\n");
    fwrite(STDERR, "    --user=sa --password=YourPassword --database=orders_test --name=orders --no-check\n\n");
    exit(1);
}

fwrite(STDOUT, "Database connection 'orders' found.\n");
fwrite(STDOUT, "----------------------------------------------\n");
