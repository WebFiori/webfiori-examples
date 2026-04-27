<?php
/**
 * PHPUnit bootstrap file.
 *
 * Initializes the WebFiori application so that the framework's autoloader,
 * configuration, and route definitions are available during test execution.
 * The database connection must already be configured in app-config.json
 * before running the test suite.
 */

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

// Verify the 'task-manager' database connection is configured.
$connection = App::getConfig()->getDBConnection('task-manager');

if ($connection === null) {
    fwrite(STDERR, "\n");
    fwrite(STDERR, "ERROR: Database connection 'task-manager' is not configured.\n");
    fwrite(STDERR, "\n");
    fwrite(STDERR, "Add it using the CLI:\n");
    fwrite(STDERR, "\n");
    fwrite(STDERR, "  php webfiori add:db-connection \\\n");
    fwrite(STDERR, "    --db-type=mssql \\\n");
    fwrite(STDERR, "    --host=localhost \\\n");
    fwrite(STDERR, "    --port=1433 \\\n");
    fwrite(STDERR, "    --user=sa \\\n");
    fwrite(STDERR, "    --password=YourPassword \\\n");
    fwrite(STDERR, "    --database=task_manager_test \\\n");
    fwrite(STDERR, "    --name=task-manager \\\n");
    fwrite(STDERR, "    --extras='{\"TrustServerCertificate\":true,\"Encrypt\":false}' \\\n");
    fwrite(STDERR, "    --no-check\n");
    fwrite(STDERR, "\n");
    fwrite(STDERR, "Then initialize and run migrations:\n");
    fwrite(STDERR, "\n");
    fwrite(STDERR, "  php webfiori migrations:ini --connection=task-manager\n");
    fwrite(STDERR, "  php webfiori migrations:run --connection=task-manager --env=dev\n");
    fwrite(STDERR, "\n");
    exit(1);
}

fwrite(STDOUT, "Database connection 'task-manager' found.\n");
fwrite(STDOUT, "----------------------------------------------\n");
