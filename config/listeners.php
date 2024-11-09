<?php

use Psr\Container\ContainerInterface;

/**
 * @param \Psr\Container\ContainerInterface $container
 * @return array<non-empty-string, \App\Util\Events\Api\EventListener[]>
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
return function (ContainerInterface $container): array {
    return [
        'route.ping' => [
            $container->get(\App\VideoConverter\Listener\PingListener::class),
        ],
    ];
};
