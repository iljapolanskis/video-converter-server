<?php

use Psr\Log\LoggerInterface;
use Slim\App;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $logger = $app->getContainer()->get(LoggerInterface::class);
    $app->addErrorMiddleware(true, true, true, $logger);
};
