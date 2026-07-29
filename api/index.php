<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ensure Vercel Serverless environment has a writable storage path
$storagePath = '/tmp/storage';
if (!is_dir($storagePath)) {
    mkdir($storagePath, 0755, true);
    mkdir($storagePath.'/app/public', 0755, true);
    mkdir($storagePath.'/framework/cache/data', 0755, true);
    mkdir($storagePath.'/framework/sessions', 0755, true);
    mkdir($storagePath.'/framework/testing', 0755, true);
    mkdir($storagePath.'/framework/views', 0755, true);
    mkdir($storagePath.'/logs', 0755, true);
}

// Force debugging and stderr logging on Vercel
$_ENV['APP_DEBUG'] = 'true';
$_ENV['LOG_CHANNEL'] = 'stderr';

// Maintenance mode check
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/../bootstrap/app.php';

// Tell Laravel to use the writable /tmp/storage directory
$app->useStoragePath($storagePath);

// Handle the request
$app->handleRequest(Request::capture());
