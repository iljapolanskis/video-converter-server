<?php

namespace App\Util\Events\Api;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Extends symfony implementation to allow passing the array in dispatch
 */
interface EventDispatcher extends EventDispatcherInterface
{
    /**
     * @param object|array $event
     * @param string|null $eventName
     * @return object
     */
    public function dispatch(object|array $event, ?string $eventName = null): object;

    /**
     * @param string $eventName
     * @param callable|array $listener
     * @param int $priority
     * @return void
     */
    public function addListener(string $eventName, callable|array $listener, int $priority = 0): void;
}
