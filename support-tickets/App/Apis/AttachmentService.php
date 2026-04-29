<?php
namespace App\Apis;

use App\Domain\Attachment;
use App\Infrastructure\Repository\AttachmentRepository;
use App\Infrastructure\Repository\TicketRepository;
use WebFiori\Database\Database;
use WebFiori\File\File;
use WebFiori\File\FileUploader;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\BadRequestException;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * REST controller for ticket file attachments.
 *
 * Handles uploading files to existing tickets and downloading attachments.
 */
#[RestController('attachments', 'Ticket attachments API')]
class AttachmentService extends WebService {
    /**
     * Downloads an attachment by ID.
     */
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Attachment ID')]
    public function downloadAttachment(?int $id = null): array {
        $db = new Database(App::getConfig()->getDBConnection('tickets'));
        $attachment = (new AttachmentRepository($db))->findById($id);

        if ($attachment === null || !file_exists($attachment->filePath)) {
            throw new NotFoundException('Attachment not found.');
        }

        $file = new File($attachment->fileName, dirname($attachment->filePath));
        $file->view(true);

        return [];
    }
    /**
     * Uploads file(s) to an existing ticket.
     *
     * The file input field name must be `file`. Allowed types:
     * pdf, doc, docx, png, jpg, jpeg, txt, zip.
     */
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'ticketId', type: ParamType::INT, description: 'Ticket ID')]
    public function uploadAttachment(?int $ticketId = null): array {
        $db = new Database(App::getConfig()->getDBConnection('tickets'));
        $ticket = (new TicketRepository($db))->findById($ticketId);

        if ($ticket === null) {
            throw new NotFoundException('Ticket not found.');
        }

        if (empty($_FILES['file'])) {
            throw new BadRequestException('No file provided. Use input name "file".');
        }

        $uploadDir = APP_PATH.'Storage'.DS.'Uploads'.DS.'tickets'.DS.$ticketId;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploader = new FileUploader();
        $uploader->setUploadDir($uploadDir);
        $uploader->addExts(['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'txt', 'zip']);
        $uploader->setAssociatedFileName('file');
        $uploadedFiles = $uploader->uploadAsFileObj(true);

        $attachments = [];
        $attRepo = new AttachmentRepository($db);

        foreach ($uploadedFiles as $file) {
            if ($file->isUploaded()) {
                $att = new Attachment(
                    ticketId: $ticket->id,
                    fileName: $file->getName(),
                    filePath: $uploadDir.DS.$file->getName(),
                    mimeType: $file->getMIME(),
                    fileSize: $file->getSize(),
                    uploadedAt: date('Y-m-d H:i:s')
                );
                $attRepo->save($att);
                $attachments[] = $att;
            }
        }

        if (empty($attachments)) {
            throw new BadRequestException('File upload failed. Check file type and size.');
        }

        return $attachments;
    }
}
