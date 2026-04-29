<?php
namespace App\Apis;

use App\Domain\Attachment;
use App\Domain\Ticket;
use App\Infrastructure\Repository\AttachmentRepository;
use App\Infrastructure\Repository\ReplyRepository;
use App\Infrastructure\Repository\TicketRepository;
use WebFiori\Database\Database;
use WebFiori\File\FileUploader;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\PutMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * REST controller for ticket operations.
 */
#[RestController('tickets', 'Support tickets API')]
class TicketService extends WebService {
    /**
     * Creates a new support ticket with optional file attachments.
     *
     * Files are uploaded from the `file` input field. Allowed types:
     * pdf, doc, docx, png, jpg, jpeg, txt, zip. Max size per file: 2MB.
     */
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'subject', type: ParamType::STRING, description: 'Ticket subject')]
    #[RequestParam(name: 'description', type: ParamType::STRING, description: 'Ticket description')]
    #[RequestParam(name: 'submitterName', type: ParamType::STRING, description: 'Your name')]
    #[RequestParam(name: 'submitterEmail', type: ParamType::EMAIL, description: 'Your email')]
    #[RequestParam(name: 'priority', type: ParamType::STRING, optional: true, default: 'medium', description: 'low, medium, or high')]
    public function createTicket(
        ?string $subject = null,
        ?string $description = null,
        ?string $submitterName = null,
        ?string $submitterEmail = null,
        ?string $priority = null
    ): array {
        $db = $this->getDb();
        $ticketRepo = new TicketRepository($db);

        $ticket = new Ticket(
            subject: $subject,
            description: $description,
            submitterName: $submitterName,
            submitterEmail: $submitterEmail,
            priority: $priority ?? 'medium',
            createdAt: date('Y-m-d H:i:s')
        );
        $ticketRepo->save($ticket);

        // Find the created ticket to get its ID
        $result = $db->table('tickets')->select()
            ->where('submitter-email', $submitterEmail)
            ->andWhere('subject', $subject)
            ->execute();
        $rows = $result->fetchAll();
        $ticketId = !empty($rows) ? (int) end($rows)['id'] : null;

        // Handle file uploads if present
        $attachments = [];

        if ($ticketId !== null && !empty($_FILES['file'])) {
            $uploadDir = APP_PATH.'Storage'.DS.'Uploads'.DS.'tickets'.DS.$ticketId;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploader = new FileUploader();
            $uploader->setUploadDir($uploadDir);
            $uploader->addExts(['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'txt', 'zip']);
            $uploader->setAssociatedFileName('file');
            $uploadedFiles = $uploader->uploadAsFileObj(true);

            $attRepo = new AttachmentRepository($db);

            foreach ($uploadedFiles as $file) {
                if ($file->isUploaded()) {
                    $att = new Attachment(
                        ticketId: $ticketId,
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
        }

        $ticket->id = $ticketId;

        // Send confirmation email to submitter
        try {
            $email = EmailHelper::create();
            $email->setSubject('Ticket #'.$ticketId.' — '.$subject);
            $email->addTo($submitterEmail, $submitterName);
            $email->insert('h2')->text('Your ticket has been received');
            $email->insert('p')->text('Ticket #'.$ticketId.': '.$subject);
            $email->insert('p')->text('Priority: '.$ticket->priority);
            $email->insert('p')->text('We will get back to you shortly.');
            $email->send();
        } catch (\Throwable $e) {
            // Email failure should not block ticket creation
        }

        return ['ticket' => $ticket, 'attachments' => $attachments];
    }
    /**
     * Lists tickets or returns a single ticket with replies and attachments.
     */
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true, description: 'Ticket ID')]
    #[RequestParam(name: 'status', type: ParamType::STRING, optional: true, description: 'Filter by status')]
    #[RequestParam(name: 'email', type: ParamType::EMAIL, optional: true, description: 'Filter by submitter email')]
    public function getTickets(?int $id = null, ?string $status = null, ?string $email = null): array {
        $db = $this->getDb();
        $repo = new TicketRepository($db);

        if ($id !== null) {
            $ticket = $repo->findById($id);

            if ($ticket === null) {
                throw new NotFoundException('Ticket not found.');
            }

            $replies = (new ReplyRepository($db))->findByTicketId($id);
            $attachments = (new AttachmentRepository($db))->findByTicketId($id);

            return ['ticket' => $ticket, 'replies' => $replies, 'attachments' => $attachments];
        }

        return $repo->findFiltered($status, $email);
    }

    /**
     * Updates ticket status.
     */
    #[PutMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Ticket ID')]
    #[RequestParam(name: 'status', type: ParamType::STRING, description: 'New status: open, in-progress, or closed')]
    public function updateStatus(?int $id = null, ?string $status = null): array {
        $repo = new TicketRepository($this->getDb());
        $ticket = $repo->findById($id);

        if ($ticket === null) {
            throw new NotFoundException('Ticket not found.');
        }

        $ticket->status = $status;
        $ticket->updatedAt = date('Y-m-d H:i:s');
        $repo->save($ticket);

        return [$ticket];
    }

    private function getDb(): Database {
        return new Database(App::getConfig()->getDBConnection('tickets'));
    }
}
