<?php

namespace App\Action\Download;

use App\Renderer\JsonRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class Compressed
{
    public function __construct(
        private JsonRenderer $renderer
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $files = scandir(APP_COMPRESSED_DIR);

        $files = array_filter($files, fn ($file) => is_file(APP_COMPRESSED_DIR . $file));
        
        // Return list of download links
        

        return $this->renderer->json($response, ['files' => $files]);
    }
}
