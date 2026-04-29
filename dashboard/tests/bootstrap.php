<?php

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

use WebFiori\Framework\App;

fwrite(STDOUT, "Initializing App...\n");

try {
    App::initiate('App', 'public', $root.'/public');
    App::start();
} catch (Throwable $e) {
    fwrite(STDOUT, "Error: ".$e->getMessage()."\n");
    exit(1);
}

fwrite(STDOUT, "Done\n----------------------------------------------\n");

$connection = App::getConfig()->getDBConnection('dashboard');

if ($connection === null) {
    fwrite(STDERR, "\nERROR: Database connection 'dashboard' is not configured.\n\n");
    fwrite(STDERR, "  php webfiori add:db-connection --db-type=mysql --host=127.0.0.1 --port=3306 \\\n");
    fwrite(STDERR, "    --user=root --password=YourPassword --database=dashboard_test --name=dashboard --no-check\n\n");
    exit(1);
}

fwrite(STDOUT, "Database connection 'dashboard' found.\n----------------------------------------------\n");
