<?php

/** @var \Slim\App $app */

$app = require_once __DIR__ . '/../config/bootstrap.php';
$om = $app->getContainer();

$context = $om->get(\Interop\Queue\Context::class);
$processor = $om->get(\App\VideoConverter\CompressProcessor::class);
$compressQueue = $om->get(\App\VideoConverter\CompressQueue::class);
$logger = $om->get(\Psr\Log\LoggerInterface::class);

while (true) {
    if ($message = $compressQueue->dequeueCompressJob()) {
        try {
            $result = $processor->process($message, $context);
            if ($result === Interop\Queue\Processor::ACK) {
                $compressQueue->acknowledgeCompressJob($message);
                $logger->info('Message processed successfully', ['message' => $message->getBody()]);
            } else {
                $logger->warning('Message not processed successfully', ['message' => $message->getBody()]);
                $compressQueue->rejectCompressJob($message);
            }
        } catch (\Throwable $e) {
            $compressQueue->rejectCompressJob($message);
            $logger->error('Failed to process message', ['exception' => $e]);
        }
    }
}
