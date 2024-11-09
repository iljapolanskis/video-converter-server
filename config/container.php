<?php

use App\Middleware\ExceptionMiddleware;
use App\Renderer\JsonRenderer;
use App\Util\Events\Api\EventDispatcher;
use App\Util\Events\Api\EventListener;
use App\Util\Twig\CustomFunctions;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Enqueue\Dbal\DbalConnectionFactory;
use Interop\Queue\Context;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use App\Util\Events\EventDispatcherImpl;
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
use Slim\Views\Twig;

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

    LoggerInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new Logger('app');

        $filename = sprintf('%s/app.log', $settings['path']);
        $level = $settings['level'];

        $fileStreamHandler = new \Monolog\Handler\StreamHandler($filename, $level, true, 0644);

        $logger->pushHandler($fileStreamHandler);

        return $logger;
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

    Twig::class => function (ContainerInterface $container) {
        $twig = Twig::create(APP_TEMPLATE_DIR, []);

        $twig->addExtension(new CustomFunctions());

        return $twig;
    },

    Connection::class => function (ContainerInterface $container) {
        $config = $container->get('settings')['doctrine'];
        return DriverManager::getConnection($config['connection']);
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
    },

    EventDispatcher::class => function (ContainerInterface $container) {
        $dispatcher = $container->get(EventDispatcherImpl::class);

        $events = (require __DIR__ . '/listeners.php')($container);

        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                if (!($listener instanceof EventListener)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Listener for event "%s" must implement EventListenerInterface. Provided: "%s"',
                        $event,
                        $listener::class
                    ));
                }

                $dispatcher->addListener($event, $listener);
            }
        }

        return $dispatcher;
    },
];
