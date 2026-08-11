<?php
namespace App\Apis;

use WebFiori\File\Exceptions\FileException;
use WebFiori\File\FileUploader;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\ApiResponse;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

/**
 * Standard multipart form file upload API.
 */
#[RestController('upload', 'Standard file upload via multipart/form-data')]
class UploadService extends WebService {

    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[ApiResponse(status: '200', description: 'File uploaded successfully')]
    #[ApiResponse(status: '422', description: 'Upload failed')]
    public function uploadFile(): array {
        $uploadDir = dirname(__DIR__) . '/Storage/Uploads';

        $uploader = new FileUploader($uploadDir, ['pdf', 'docx', 'xlsx', 'txt', 'png', 'jpg']);
        $uploader->setAssociatedFileName('file');
        $uploader->setMaxFileSize(10 * 1024 * 1024); // 10MB

        $results = $uploader->upload(true);

        $uploaded = [];
        $errors = [];

        foreach ($results as $fileInfo) {
            if ($fileInfo['uploaded']) {
                $uploaded[] = [
                    'name' => $fileInfo['name'],
                    'size' => $fileInfo['size'],
                    'mime' => $fileInfo['mime'],
                ];
            } else {
                $errors[] = [
                    'name' => $fileInfo['name'],
                    'error' => $fileInfo['upload-error'],
                ];
            }
        }

        return [
            'uploaded' => $uploaded,
            'errors' => $errors,
        ];
    }

    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[ApiResponse(status: '200', description: 'List uploaded files')]
    public function listFiles(): array {
        $uploadDir = dirname(__DIR__) . '/Storage/Uploads';
        $files = [];

        if (is_dir($uploadDir)) {
            foreach (scandir($uploadDir) as $file) {
                if ($file !== '.' && $file !== '..' && is_file($uploadDir . '/' . $file)) {
                    $files[] = [
                        'name' => $file,
                        'size' => filesize($uploadDir . '/' . $file),
                    ];
                }
            }
        }

        return ['files' => $files];
    }
}
