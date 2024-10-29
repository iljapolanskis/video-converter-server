<?php

namespace App\VideoConverter\Job;

use App\Util\DtoTrait;

final readonly class CompressJob
{
    use DtoTrait;

    public function __construct(
        public ?string $id,
        public string $filename,
        public int $progress = 0,
    ) {
    }
}
