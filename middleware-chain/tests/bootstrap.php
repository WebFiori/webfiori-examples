<?php
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

use WebFiori\Framework\App;

App::initiate('App', 'public', $root . '/public');
App::start();
