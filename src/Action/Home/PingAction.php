<?php

namespace App\Action\Home;

use App\Renderer\JsonRenderer;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class PingAction
{
    public function __construct(
        private JsonRenderer $renderer,
        private EntityManager $orm,
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $jobs = $this->orm->getConnection()->executeQuery('SELECT * FROM enqueue')->fetchAllAssociative();
        return $this->renderer->json($response, ['success' => true, 'jobs' => []]);
    }
}
