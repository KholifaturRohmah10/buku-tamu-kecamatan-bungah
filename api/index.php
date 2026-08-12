<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ensure Vercel Serverless environment has a writable storage path
$storagePath = '/tmp/storage';
$dirs = [
    '',
    '/app',
    '/app/public',
    '/framework',
    '/framework/cache',
    '/framework/cache/data',
    '/framework/sessions',
    '/framework/testing',
    '/framework/views',
    '/logs'
];
foreach ($dirs as $dir) {
    if (!is_dir($storagePath . $dir)) {
        mkdir($storagePath . $dir, 0755, true);
    }
}

// Create bootstrap cache directory
$bootstrapCachePath = '/tmp/storage/bootstrap/cache';
if (!is_dir($bootstrapCachePath)) {
    mkdir($bootstrapCachePath, 0755, true);
}

// STRICTLY use isrgrootx1.pem for TiDB Cloud Serverless on Vercel to avoid Let's Encrypt cross-signing bugs on AWS Lambda
$caPath = __DIR__.'/../isrgrootx1.pem';

// Inject crucial environment variables manually to ensure foolproof Vercel execution
$envVars = [
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'true',
    'APP_KEY' => 'base64:loeSxfmziYhfKMrfV1zrg5jPQ9ird6mn+pMakeEJuAw=',
    'LOG_CHANNEL' => 'stderr',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com',
    'DB_PORT' => '4000',
    'DB_DATABASE' => 'buku_tamu',
    'DB_USERNAME' => '7i7y5jnB4C5ez9W.root',
    'DB_PASSWORD' => 'ZvaAF9CCIiE6cpQr',
    'MYSQL_ATTR_SSL_CA' => file_exists($caPath) ? $caPath : '',
    'APP_SERVICES_CACHE' => '/tmp/storage/bootstrap/cache/services.php',
    'APP_PACKAGES_CACHE' => '/tmp/storage/bootstrap/cache/packages.php',
    'APP_CONFIG_CACHE' => '/tmp/storage/bootstrap/cache/config.php',
    'APP_ROUTES_CACHE' => '/tmp/storage/bootstrap/cache/routes.php',
    'APP_EVENTS_CACHE' => '/tmp/storage/bootstrap/cache/events.php',
];

foreach ($envVars as $key => $value) {
    putenv("$key=$value");
    $_SERVER[$key] = $value;
    $_ENV[$key] = $value;
}

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

// Force known-working SSL configuration for TiDB Serverless after app boots
$app->booted(function() use ($caPath) {
    $options = [1014 => false];
    if (file_exists($caPath)) {
        $options[1009] = $caPath;
    }
    config(['database.connections.mysql.options' => $options]);
});

// Handle the request with a global try-catch to intercept Exception Handler crashes
try {
    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    echo "<h1>CRITICAL ERROR CAUGHT BY VERCEL WRAPPER</h1>";
    echo "<pre>" . htmlspecialchars((string) $e) . "</pre>";
}
