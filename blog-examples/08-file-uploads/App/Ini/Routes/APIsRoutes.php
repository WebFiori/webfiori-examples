<?php

namespace App\Ini\Routes;

use WebFiori\File\Exceptions\FileException;
use WebFiori\File\ResumableUploader;
use WebFiori\File\StreamingUploader;
use WebFiori\Framework\App;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Router\ServiceRouter;

class APIsRoutes {
    public static function create() {
        // Standard form upload (multipart/form-data) — works with WebServicesManager
        ServiceRouter::discover('App\\Apis', '/apis', [
            RouteOption::MIDDLEWARE => []
        ]);

        // Streaming upload — raw binary body (application/octet-stream)
        Router::api([
            RouteOption::PATH => '/apis/stream-upload',
            RouteOption::TO => function () {
                $uploadDir = dirname(__DIR__, 2) . '/Storage/Uploads';
                $response = App::getResponse();
                $response->addHeader('Content-Type', 'application/json');

                $uploader = new StreamingUploader($uploadDir, ['pdf', 'docx', 'xlsx', 'txt', 'png', 'jpg']);
                $uploader->setMaxFileSize(50 * 1024 * 1024); // 50MB

                try {
                    $file = $uploader->receive(); // filename from headers

                    $response->setCode(201);
                    $response->write(json_encode([
                        'data' => [
                            'name' => $file->getName(),
                            'size' => filesize($file->getAbsolutePath()),
                        ]
                    ]));
                } catch (FileException $e) {
                    $response->setCode(422);
                    $response->write(json_encode(['error' => $e->getMessage()]));
                }
            },
            RouteOption::REQUEST_METHODS => ['POST'],
        ]);

        // Resumable chunk upload — POST receives chunk, GET checks offset, DELETE cancels
        Router::api([
            RouteOption::PATH => '/apis/chunk-upload',
            RouteOption::TO => function () {
                $uploadDir = dirname(__DIR__, 2) . '/Storage/Uploads';
                $uploader = new ResumableUploader($uploadDir, ['pdf', 'docx', 'xlsx', 'txt', 'png', 'jpg', 'mp4', 'zip']);
                $uploader->setMaxFileSize(500 * 1024 * 1024); // 500MB

                $response = App::getResponse();
                $response->addHeader('Content-Type', 'application/json');
                $method = $_SERVER['REQUEST_METHOD'];

                if ($method === 'GET') {
                    $uploadId = $_GET['upload-id'] ?? '';
                    $filename = $_GET['filename'] ?? '';
                    $offset = $uploader->getOffset($uploadId, $filename);
                    $response->write(json_encode(['data' => ['offset' => $offset]]));
                } elseif ($method === 'POST') {
                    $uploadId = $_SERVER['HTTP_X_UPLOAD_ID'] ?? '';
                    $filename = $_SERVER['HTTP_X_FILENAME'] ?? null;
                    $isLast = ($_SERVER['HTTP_X_IS_LAST'] ?? '0') === '1';

                    try {
                        $result = $uploader->receiveChunk($uploadId, $filename, $isLast);

                        $response->setCode($result['complete'] ? 201 : 200);
                        $response->write(json_encode([
                            'data' => [
                                'offset' => $result['offset'],
                                'complete' => $result['complete'],
                                'file' => $result['file'] ? $result['file']->getName() : null,
                            ]
                        ]));
                    } catch (FileException $e) {
                        $response->setCode(422);
                        $response->write(json_encode(['error' => $e->getMessage()]));
                    }
                } elseif ($method === 'DELETE') {
                    $uploadId = $_GET['upload-id'] ?? '';
                    $filename = $_GET['filename'] ?? '';
                    $uploader->cancel($uploadId, $filename);
                    $response->write(json_encode(['data' => ['cancelled' => true]]));
                }
            },
            RouteOption::REQUEST_METHODS => ['GET', 'POST', 'DELETE'],
        ]);
    }
}
