<?php

namespace App\VideoConverter\Listener;

use App\Util\Events\Api\EventListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final readonly class PingListener implements EventListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function __invoke(GenericEvent $event): GenericEvent
    {
        $this->logger->info('Executed', $event->getArguments());

        return $event;
    }
}
