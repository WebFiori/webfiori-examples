<?php

/**
 * PHPUnit bootstrap for the blog application.
 */
$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

use WebFiori\Framework\App;

fwrite(STDOUT, "Initializing App...\n");

try {
    App::initiate('App', 'public', $root.'/public');
    App::start();
} catch (Throwable $e) {
    fwrite(STDOUT, "Error During Initialization: ".$e->getMessage()."\n");
    exit(1);
}

fwrite(STDOUT, "Done\n");
fwrite(STDOUT, "----------------------------------------------\n");

$connection = App::getConfig()->getDBConnection('blog');

if ($connection === null) {
    fwrite(STDERR, "\nERROR: Database connection 'blog' is not configured.\n\n");
    fwrite(STDERR, "Add it using:\n\n");
    fwrite(STDERR, "  php webfiori add:db-connection --db-type=mysql --host=127.0.0.1 --port=3306 \\\n");
    fwrite(STDERR, "    --user=root --password=YourPassword --database=blog_test --name=blog --no-check\n\n");
    fwrite(STDERR, "Then run migrations:\n\n");
    fwrite(STDERR, "  php webfiori migrations:ini --connection=blog\n");
    fwrite(STDERR, "  php webfiori migrations:run --connection=blog --env=dev\n\n");
    exit(1);
}

fwrite(STDOUT, "Database connection 'blog' found.\n");
fwrite(STDOUT, "----------------------------------------------\n");
