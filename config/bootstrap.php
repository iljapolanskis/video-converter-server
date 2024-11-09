<?php

use DI\ContainerBuilder;
use Slim\App;

const APP_BASE_PATH = __DIR__ . '/../';

const APP_TEMP_PATH = APP_BASE_PATH . 'tmp/';
const APP_UPLOAD_DIR = APP_TEMP_PATH . 'uploads/';
const APP_CHUNKS_DIR = APP_TEMP_PATH . 'chunks/';
const APP_COMPRESSED_DIR = APP_TEMP_PATH . 'compressed/';
const APP_READY_DIR = APP_TEMP_PATH . 'ready/';

const APP_DB_DIR = APP_BASE_PATH . 'database/';
const APP_DB_CACHE_DIR = APP_DB_DIR . 'cache/';
const APP_DB_PROXY_DIR = APP_DB_DIR . 'proxy/';
const APP_DB_METADATA_DIR = APP_DB_DIR . 'metadata/';

const APP_TEMPLATE_DIR = APP_BASE_PATH . 'view/html/';
const APP_TEMPLATE_CACHE_DIR = APP_BASE_PATH . 'view/cache/';

require_once __DIR__ . '/../vendor/autoload.php';

// Build DI container instance
$container = (new ContainerBuilder())
    ->addDefinitions(__DIR__ . '/container.php')
    ->build();

// Create App instance
return $container->get(App::class);
