<?php

namespace App\Action\Dashboard;

use App\VideoConverter\Job\CompressJob;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

final readonly class Overview
{
    public function __construct(
        private Twig $twig,
        private EntityManager $orm
    ) {
    }

    private function getCompressJobs(): array
    {
        $messages = $this->orm->getConnection()
            ->executeQuery('SELECT * FROM enqueue WHERE queue = \'videoCompressQueue\'')
            ->fetchAllAssociative();

        $jobs = [];
        foreach ($messages as $message) {
            $parsedBody = json_decode($message['body'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $filename = $message['body'];
                $progress = 0;
            } else {
                $filename = $parsedBody['filename'];
                $progress = $parsedBody['progress'];
            }
            $jobs[] = new CompressJob(id: $message['id'], filename: $filename, progress: $progress);
        }

        return $jobs;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // TODO: Move this into repository class
        $messages = $this->orm->getConnection()->executeQuery('SELECT * FROM enqueue')->fetchAllAssociative();

        $viewData = [
            'name' => $args['name'] ?? 'Guest',
            'messages' => $messages,
            'jobs' => $this->getCompressJobs(),
        ];

        return $this->twig->render($response, 'dashboard/overview.twig', $viewData);
    }
}
