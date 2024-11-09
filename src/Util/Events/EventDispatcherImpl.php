<?php

namespace App\Util\Events;

use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Extends symfony implementation to allow passing the array in dispatch
 */
class EventDispatcherImpl implements Api\EventDispatcher
{
    public function __construct(
        private SymfonyEventDispatcher $symfonyDispatcher
    ) {
    }

    public function dispatch(object|array $event, ?string $eventName = null): object
    {
        if (is_array($event)) {
            $event = (new GenericEvent())->setArguments(['data' => $event]);
        }

        return $this->symfonyDispatcher->dispatch($event, $eventName);
    }

    public function addListener(string $eventName, callable|array $listener, int $priority = 0): void
    {
        $this->symfonyDispatcher->addListener($eventName, $listener, $priority);
    }
}
