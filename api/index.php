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

// Write the required TiDB SSL certificate to /tmp to guarantee it exists for PDO
$cert = <<<EOT
-----BEGIN CERTIFICATE-----
MIIFazCCA1OgAwIBAgIRAIIQz7DSQONZRGPgu2OCiwAwDQYJKoZIhvcNAQELBQAw
TzELMAkGA1UEBhMCVVMxKTAnBgNVBAoTIEludGVybmV0IFNlY3VyaXR5IFJlc2Vh
cmNoIEdyb3VwMRUwEwYDVQQDEwxJU1JHIFJvb3QgWDEwHhcNMTUwNjA0MTEwNDM4
WhcNMzUwNjA0MTEwNDM4WjBPMQswCQYDVQQGEwJVUzEpMCcGA1UEChMgSW50ZXJu
ZXQgU2VjdXJpdHkgUmVzZWFyY2ggR3JvdXAxFTATBgNVBAMTDElTUkcgUm9vdCBY
MTCCAiIwDQYJKoZIhvcNAQEBBQADggIPADCCAgoCggIBAK3oJHP0FDfzm54rVygc
h77ct984kIxuPOZXoHj3dcKi/vVqbvYATyjb3miGbESTtrFj/RQSa78f0uoxmyF+
0TM8ukj13Xnfs7j/EvEhmkvBioZxaUpmZmyPfjxwv60pIgbz5MDmgK7iS4+3mX6U
A5/TR5d8mUgjU+g4rk8Kb4Mu0UlXjIB0ttov0DiNewNwIRt18jA8+o+u3dpjq+sW
T8KOEUt+zwvo/7V3LvSye0rgTBIlDHCNAymg4VMk7BPZ7hm/ELNKjD+Jo2FR3qyH
B5T0Y3HsLuJvW5iB4YlcNHlsdu87kGJ55tukmi8mxdAQ4Q7e2RCOFvu396j3x+UC
B5iPNgiV5+I3lg02dZ77DnKxHZu8A/lJBdiB3QW0KtZB6awBdpUKD9jf1b0SHzUv
KBds0pjBqAlkd25HN7rOrFleaJ1/ctaJxQZBKT5ZPt0m9STJEadao0xAH0ahmbWn
OlFuhjuefXKnEgV4We0+UXgVCwOPjdAvBbI+e0ocS3MFEvzG6uBQE3xDk3SzynTn
jh8BCNAw1FtxNrQHusEwMFxIt4I7mKZ9YIqioymCzLq9gwQbooMDQaHWBfEbwrbw
qHyGO0aoSCqI3Haadr8faqU9GY/rOPNk3sgrDQoo//fb4hVC1CLQJ13hef4Y53CI
rU7m2Ys6xt0nUW7/vGT1M0NPAgMBAAGjQjBAMA4GA1UdDwEB/wQEAwIBBjAPBgNV
HRMBAf8EBTADAQH/MB0GA1UdDgQWBBR5tFnme7bl5AFzgAiIyBpY9umbbjANBgkq
hkiG9w0BAQsFAAOCAgEAVR9YqbyyqFDQDLHYGmkgJykIrGF1XIpu+ILlaS/V9lZL
ubhzEFnTIZd+50xx+7LSYK05qAvqFyFWhfFQDlnrzuBZ6brJFe+GnY+EgPbk6ZGQ
3BebYhtF8GaV0nxvwuo77x/Py9auJ/GpsMiu/X1+mvoiBOv/2X/qkSsisRcOj/KK
NFtY2PwByVS5uCbMiogziUwthDyC3+6WVwW6LLv3xLfHTjuCvjHIInNzktHCgKQ5
ORAzI4JMPJ+GslWYHb4phowim57iaztXOoJwTdwJx4nLCgdNbOhdjsnvzqvHu7Ur
TkXWStAmzOVyyghqpZXjFaH3pO3JLF+l+/+sKAIuvtd7u+Nxe5AW0wdeRlN8NwdC
jNPElpzVmbUq4JUagEiuTDkHzsxHpFKVK7q4+63SM1N95R1NbdWhscdCb+ZAJzVc
oyi3B43njTOQ5yOf+1CceWxG1bQVs5ZufpsMljq4Ui0/1lvh+wjChP4kqKOJ2qxq
4RgqsahDYVvTH9w7jXbyLeiNdd8XM2w9U/t7y0Ff/9yi0GE44Za4rF2LN9d11TPA
mRGunUHBcnWEvgJBQl9nJEiU0Zsnvgc/ubhPgXRR4Xq37Z0j4r7g1SgEEzwxA57d
emyPxgcYxn/eR44/KJ4EBs+lVDR3veyJm+kXQ99b21/+jh5Xos1AnX5iItreGCc=
-----END CERTIFICATE-----
EOT;
$caPath = '/tmp/isrgrootx1.pem';
if (!file_exists($caPath)) {
    file_put_contents($caPath, $cert);
}

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
