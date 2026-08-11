<?php

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

putenv('APP_ENV=testing');

\App\Ini\Privileges::initialize();
