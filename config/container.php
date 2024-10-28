<?php

use App\Middleware\ExceptionMiddleware;
use App\Renderer\JsonRenderer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Enqueue\Dbal\DbalConnectionFactory;
use Interop\Queue\Context;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;

return [
    'settings' => fn () => require __DIR__ . '/settings.php',

    App::class => function (ContainerInterface $container) {
        $app = AppFactory::createFromContainer($container);
        (require __DIR__ . '/routes.php')($app);
        (require __DIR__ . '/middleware.php')($app);

        return $app;
    },

    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },

    ServerRequestFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },

    StreamFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },

    UploadedFileFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },

    UriFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },

    RouteParserInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getRouteCollector()->getRouteParser();
    },

    BasePathMiddleware::class => function (ContainerInterface $container) {
        return new BasePathMiddleware($container->get(App::class));
    },

    LoggerInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new Logger('app');

        $filename = sprintf('%s/app.log', $settings['path']);
        $level = $settings['level'];
        $rotatingFileHandler = new RotatingFileHandler($filename, 0, $level, true, 0777);
        $rotatingFileHandler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($rotatingFileHandler);

        return $logger;
    },

    Connection::class => function (ContainerInterface $container) {
        $config = $container->get('settings')['doctrine'];
        return DriverManager::getConnection($config['connection']);
    },

    ExceptionMiddleware::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['error'];

        return new ExceptionMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $container->get(JsonRenderer::class),
            $container->get(LoggerInterface::class),
            (bool)$settings['display_error_details'],
        );
    },

    EntityManager::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['doctrine'];

        $config = ORMSetup::createXMLMetadataConfiguration(
            $settings['metadata_dirs'],
            $settings['dev_mode'],
            $settings['proxy_dir']
        );

        $connection = DriverManager::getConnection($settings['connection']);

        return new EntityManager($connection, $config);
    },

    DbalConnectionFactory::class => function (ContainerInterface $container) {
        $setting = $container->get('settings')['doctrine'];
        return new DbalConnectionFactory($setting);
    },

    Context::class => function (ContainerInterface $container) {
        return $container->get(DbalConnectionFactory::class)->createContext();
    }
];
