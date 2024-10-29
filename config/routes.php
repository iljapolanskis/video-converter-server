<?php

// Define app routes

use Slim\App;

return function (App $app) {
    $app->get('/', \App\Action\Home\HomeAction::class)->setName('home');
    $app->get('/ping', \App\Action\Home\PingAction::class);
    $app->post('/upload/stream', \App\Action\Upload\Stream::class);
    $app->get('/download/compressed', \App\Action\Download\Compressed::class);
    $app->get('/download/once', \App\Action\Download\Compressed::class);

    $app->group('/dashboard', function ($app) {
        $app->get('/overview', \App\Action\Dashboard\Overview::class);
    });
};
