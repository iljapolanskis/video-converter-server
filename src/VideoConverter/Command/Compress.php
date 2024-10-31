<?php

declare(strict_types=1);

namespace App\VideoConverter\Command;

use App\VideoConverter\Codec\X265;
use App\VideoConverter\Job\CompressJob;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFMpeg;
use FFMpeg\Filters\Audio\SimpleFilter;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Filters\Video\SynchronizeFilter;
use Psr\Log\LoggerInterface;

final readonly class Compress
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManager $orm,
    ) {
    }

    public function execute(CompressJob $job): void
    {
        $ffmpeg = FFMpeg::create([
            'timeout' => 0
        ]);

        $video = $ffmpeg->open(APP_UPLOAD_DIR . $job->filename);

        $video->addFilter(new ResizeFilter(new Dimension(1920, 1080)));
        $video->addFilter(new SynchronizeFilter());
        $video->addFilter(new SimpleFilter([
            '-crf', '26',
            '-preset', 'veryfast'
        ]));
        $format = (new X265())->on('progress', function ($video, $format, $percentage) use ($job) {
            static $lastPercentage = 0;
            if ($percentage > $lastPercentage) {
                $this->updateProgress($job, (int)$percentage);
                $lastPercentage = $percentage;
            }
        });

        $video->save($format, APP_COMPRESSED_DIR . $job->filename);
        $this->logger->info("Successfully Compressed \"$job->filename\"");

        unlink(APP_UPLOAD_DIR . $job->filename);
    }

    /**
     * @param \App\VideoConverter\Job\CompressJob $job
     * @param int $progress
     * @return void
     */
    private function updateProgress(CompressJob $job, int $progress): void
    {
        try {
            $body = json_encode(['filename' => $job->filename, 'progress' => $progress]);
            $this->orm->getConnection()->executeQuery('UPDATE enqueue SET body = ? WHERE id = ?', [$body, $job->id]);
        } catch (Exception $e) {
            $this->logger->error(
                'Failed to update progress',
                ['id' => $job->id, 'filename' => $job->filename, 'progress' => $progress]
            );
        }
    }
}
