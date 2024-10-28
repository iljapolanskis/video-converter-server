<?php

namespace App\VideoConverter;

use App\VideoConverter\Command\Compress;
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
        $this->compress->execute($message->getBody());

        return self::ACK;
    }
}
