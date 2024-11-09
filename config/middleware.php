<?php

use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

return function (App $app): void {
    $app->add(TwigMiddleware::create($app, $app->getContainer()->get(Twig::class)));
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->addErrorMiddleware(true, true, true, $app->getContainer()->get(LoggerInterface::class));
};
