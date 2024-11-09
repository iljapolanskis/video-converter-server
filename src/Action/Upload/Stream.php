<?php

namespace App\Action\Upload;

use App\VideoConverter\CompressQueue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class Stream
{
    public const string HEADER_FILE_ID = 'X-File-ID';
    public const string HEADER_FILE_NAME = 'X-File-Name';
    public const string HEADER_FILE_SIZE = 'X-File-Size';

    public function __construct(
        private CompressQueue $queue
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!is_dir(APP_UPLOAD_DIR)) {
            mkdir(APP_UPLOAD_DIR, 0644, true);
        }

        if (!is_dir(APP_CHUNKS_DIR)) {
            mkdir(APP_CHUNKS_DIR, 0644, true);
        }

        $contentRange = $request->getHeaderLine('Content-Range');
        if (preg_match('/bytes (\d+)-(\d+)/', $contentRange, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
        } else {
            $response->getBody()->write('Invalid Content-Range header');
            return $response->withStatus(400);
        }

        $fileName = $request->getHeaderLine(self::HEADER_FILE_NAME);
        $uploadId = $request->getHeaderLine(self::HEADER_FILE_ID);
        $fileSize = (int)$request->getHeaderLine(self::HEADER_FILE_SIZE);

        // TODO: Make it one time check for each upload & make it global, so it works across multiple uploads
        if (disk_free_space(APP_UPLOAD_DIR) < $fileSize + (1024 * 1024)) {
            $response->getBody()->write('Not enough free space on server');
            return $response->withStatus(500);
        }

        $body = $request->getBody()->getContents();
        $chunkFilePath = APP_CHUNKS_DIR . $fileName . $uploadId;

        file_put_contents($chunkFilePath, $body, FILE_APPEND);

        if ($this->isEof($fileSize, $end)) {
            $finalFilePath = APP_UPLOAD_DIR . $fileName;
            rename($chunkFilePath, $finalFilePath);

            $this->queue->enqueueCompressJob($fileName);
        }

        $response->getBody()->write('Chunk uploaded successfully');
        return $response->withStatus(200);
    }

    private function validateRequest(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $contentRange = $request->getHeaderLine('Content-Range');
        if (preg_match('/bytes (\d+)-(\d+)/', $contentRange, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
        } else {
            $response->getBody()->write('Invalid Content-Range header');
            return $response->withStatus(400);
        }

        $fileName = $request->getHeaderLine(self::HEADER_FILE_NAME);
        $uploadId = $request->getHeaderLine(self::HEADER_FILE_ID);
        $fileSize = (int)$request->getHeaderLine(self::HEADER_FILE_SIZE);

        if (!$fileName || !$uploadId || !$fileSize) {
            $headers = json_encode([self::HEADER_FILE_SIZE, self::HEADER_FILE_ID, self::HEADER_FILE_NAME]);
            $response->getBody()->write('Missing required headers: ' . $headers);
            return $response->withStatus(400);
        }

        if (disk_free_space(APP_UPLOAD_DIR) < $fileSize + (1024 * 1024)) {
            $response->getBody()->write('Not enough free space on server');
            return $response->withStatus(500);
        }

        return $response;
    }

    private function isEof(int $fileSize, int $contentEnd): bool
    {
        return $contentEnd + 1 === $fileSize;
    }
}
