<?php

/** @var \Slim\App $app */
$app = require_once __DIR__ . '/../config/bootstrap.php';
$om = $app->getContainer();

if (!file_exists(__DIR__ . '/database.sqlite')) {
    touch(__DIR__ . '/database.sqlite');
}

/** @var \Enqueue\Dbal\DbalContext $context */
$context = $om->get(\Interop\Queue\Context::class);

$context->createDataBaseTable();
