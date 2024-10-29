<?php

namespace App\VideoConverter;

use App\VideoConverter\Command\Compress;
use App\VideoConverter\Job\CompressJob;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;

final readonly class CompressProcessor implements Processor
{
    public function __construct(
        private Compress $compress
    ) {
    }

    public function process(Message $message, Context $context): string
    {
        $job = $this->convertMessageToJob($message);
        $this->compress->execute($job);

        return self::ACK;
    }
    private function convertMessageToJob(Message $message): CompressJob
    {
        $parsedBody = json_decode($message->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $filename = $message->getBody();
            $progress = 0;
        } else {
            $filename = $parsedBody['filename'];
            $progress = $parsedBody['progress'];
        }
        $job = new CompressJob(id: $message->getMessageId(), filename: $filename, progress: $progress);

        return $job;
    }
}
