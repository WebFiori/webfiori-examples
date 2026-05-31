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

$connection = App::getConfig()->getDBConnection('booking');

if ($connection === null) {
    fwrite(STDERR, "\nERROR: Database connection 'booking' is not configured.\n\n");
    exit(1);
}

fwrite(STDOUT, "Database connection 'booking' found.\n");
fwrite(STDOUT, "----------------------------------------------\n");
