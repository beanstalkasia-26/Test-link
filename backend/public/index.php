<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (is_file($release = __DIR__.'/../release.txt')
    && @file_get_contents(__DIR__.'/../storage/app/deployed-release') !== trim((string) file_get_contents($release))) {
    require __DIR__.'/../bootstrap/apply-release.php';
}

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
