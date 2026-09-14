<?php
/**
 * Bootstrap for session tests.
 * 
 * Initializes the WebFiori App so SessionManager can access request/response.
 */

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

use WebFiori\Framework\App;
use WebFiori\Framework\Autoload\ClassLoader;

// Set up REQUEST_METHOD before anything else
$_SERVER['REQUEST_METHOD'] = 'GET';
putenv('REQUEST_METHOD=GET');

// Initialize autoloader with root path definition
ClassLoader::get([
    'search-folders' => ['App', 'vendor'],
    'define-root'    => true,
    'root'           => $root,
    'on-load-failure' => 'do-nothing',
]);

// Initialize and start application
App::initiate('App', 'public', $root);
App::start();

putenv('APP_ENV=testing');
