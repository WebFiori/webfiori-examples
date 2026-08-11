<?php

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

use App\Session\ArraySessionStorage;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;

putenv('APP_ENV=testing');

fwrite(STDOUT, "Initializing App...\n");

try {
    App::initiate('App', 'public', $root . '/public');
    App::start();
} catch (Throwable $e) {
    fwrite(STDOUT, "Error During Initialization: " . $e->getMessage() . "\n");
    exit(1);
}

// Override session storage with in-memory driver after app init
SessionsManager::setStorage(new ArraySessionStorage());
SessionsManager::reset();

fwrite(STDOUT, "Done\n");
fwrite(STDOUT, "----------------------------------------------\n");
