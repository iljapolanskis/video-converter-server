<?php

use Slim\App;

return function (App $app): void {
    $app->get('/', \App\Action\Home\HomeAction::class)->setName('home');
    $app->get('/ping', \App\Action\Home\PingAction::class);
    $app->post('/upload/stream', \App\Action\Upload\Stream::class);

    $app->group('/download', function ($app): void {
        $app->get('/compressed', \App\Action\Download\Compressed::class);
        $app->get('/once', \App\Action\Download\Once::class);
    });

    $app->group('/dashboard', function ($app): void {
        $app->get('/overview', \App\Action\Dashboard\Overview::class);
    });
};
