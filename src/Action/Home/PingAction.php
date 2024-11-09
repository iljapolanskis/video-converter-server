<?php

namespace App\Action\Home;

use App\Renderer\JsonRenderer;
use App\Util\Events\Api\EventDispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class PingAction
{
    public function __construct(
        private JsonRenderer $renderer,
        private EventDispatcher $dispatcher,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->dispatcher->dispatch(['Hello', 'World'], 'route.ping');

        return $this->renderer->json($response, ['success' => true]);
    }
}
