<?php

namespace App\VideoConverter;

use Interop\Queue\Consumer;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Producer;
use Interop\Queue\Queue;
use Psr\Log\LoggerInterface;

final readonly class CompressQueue
{
    private Queue $queue;
    private Producer $producer;
    private Consumer $consumer;

    public function __construct(
        private Context $queueContext,
        private LoggerInterface $logger
    ) {
        $this->producer = $this->queueContext->createProducer();
        $this->queue = $this->queueContext->createQueue('videoCompressQueue');
        $this->consumer = $this->queueContext->createConsumer($this->queue);
    }

    public function enqueueCompressJob(string $filePath): bool
    {
        try {
            $message = $this->queueContext->createMessage($filePath);
            $this->producer->send($this->queue, $message);
        } catch (\Throwable $t) {
            $this->logger->error('[CompressQueue] Failed to add video to queue', ['file' => $filePath]);
            return false;
        }

        return true;
    }

    public function dequeueCompressJob(): Message|null
    {
        $message = $this->consumer->receive();

        return $message;
    }

    public function acknowledgeCompressJob(Message $message): void
    {
        $this->consumer->acknowledge($message);
    }

    public function rejectCompressJob(Message $message): void
    {
        $this->consumer->reject($message);
    }
}
