<?php

declare(strict_types=1);

namespace App\VideoConverter\Command;

use App\VideoConverter\Codec\X265;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFMpeg;
use FFMpeg\Filters\Audio\SimpleFilter;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Filters\Video\SynchronizeFilter;
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

        $video->addFilter(new ResizeFilter(new Dimension(1920, 1080)));
        $video->addFilter(new SynchronizeFilter());
        $video->addFilter(new SimpleFilter([
            '-crf', '26',
            '-preset', 'veryfast'
        ]));
        $format = (new X265())->on('progress', function ($video, $format, $percentage) use ($filename) {
            static $lastPercentage = 0;
            if ($percentage > $lastPercentage) {
                $this->logger->info("Progress \"$filename\" $percentage");
                $lastPercentage = $percentage;
            }
        });

        $video->save($format, APP_COMPRESSED_DIR . $filename);
        $this->logger->info("Successfully Compressed \"$filename\"");

        unlink(APP_UPLOAD_DIR . $filename);
    }
}
