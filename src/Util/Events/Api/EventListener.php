<?php

namespace App\Util\Events\Api;

use Symfony\Component\EventDispatcher\GenericEvent;

interface EventListener
{
    public function __invoke(GenericEvent $event): GenericEvent;
}
