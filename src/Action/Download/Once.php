<?php

namespace App\Action\Download;

use App\Util\Http\Response\JsonRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Download file once & delete it on the server
 */
final readonly class Once
{
    public function __construct(
        private JsonRenderer $renderer
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->renderer->json($response, ['file' => 'not_implemented_yet']);
    }
}
