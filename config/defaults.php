<?php

error_reporting(0);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

date_default_timezone_set('Europe/Riga');

$settings = [];

$settings['error'] = [
    'display_error_details' => false, // Should be set to false for the production environment
];

$settings['logger'] = [
    'path' => __DIR__ . '/../logs',     // Log file location
    'level' => Psr\Log\LogLevel::DEBUG,    // Default log level
];

$settings['doctrine'] = [
    // Enables or disables Doctrine metadata caching
    // for either performance or convenience during development.
    'dev_mode' => true,
    'proxy_dir' => APP_DB_PROXY_DIR,

    'cache_dir' => APP_DB_CACHE_DIR,

    'metadata_dirs' => [APP_DB_METADATA_DIR],

    'connection' => [
        'driver' => 'pdo_sqlite',
        'path' => APP_DB_DIR . 'database.sqlite',
        'user' => 'db',
        'password' => 'db',
    ],
];

return $settings;
