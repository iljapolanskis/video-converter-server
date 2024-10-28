<?php

declare(strict_types=1);

namespace App\VideoConverter\Command;

use App\VideoConverter\Codec\X265;
use FFMpeg\FFMpeg;
use FFMpeg\Filters\Audio\SimpleFilter;
use Psr\Log\LoggerInterface;

final readonly class Compress
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function execute(string $filename): void
    {
        $ffmpeg = FFMpeg::create([
            'timeout' => 0
        ]);

        $video = $ffmpeg->open(APP_UPLOAD_DIR . $filename);
        $video->addFilter(new SimpleFilter(['-crf', '26', '-preset', 'veryfast']));

        $format = (new X265())->on('progress', function ($video, $format, $percentage) {
            static $lastPercentage = 0;
            if ($percentage > $lastPercentage) {
                $this->logger->info("Progress $percentage");
                $lastPercentage = $percentage;
            }
        });

        $video->save($format, APP_COMPRESSED_DIR . $filename);

        unlink(APP_UPLOAD_DIR . $filename);
    }
}
