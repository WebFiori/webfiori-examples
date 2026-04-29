<?php
namespace App\Apis;

use App\Domain\Reply;
use App\Infrastructure\Repository\ReplyRepository;
use App\Infrastructure\Repository\TicketRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * REST controller for ticket replies.
 */
#[RestController('replies', 'Ticket replies API')]
class ReplyService extends WebService {
    /**
     * Adds a reply to a ticket.
     */
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'ticketId', type: ParamType::INT, description: 'Ticket ID')]
    #[RequestParam(name: 'authorName', type: ParamType::STRING, description: 'Author name')]
    #[RequestParam(name: 'content', type: ParamType::STRING, description: 'Reply content')]
    public function addReply(?int $ticketId = null, ?string $authorName = null, ?string $content = null): array {
        $db = new Database(App::getConfig()->getDBConnection('tickets'));
        $ticket = (new TicketRepository($db))->findById($ticketId);

        if ($ticket === null) {
            throw new NotFoundException('Ticket not found.');
        }

        $reply = new Reply(
            ticketId: $ticket->id,
            authorName: $authorName,
            content: $content,
            createdAt: date('Y-m-d H:i:s')
        );

        (new ReplyRepository($db))->save($reply);

        // Notify the ticket submitter about the new reply
        try {
            $email = EmailHelper::create();
            $email->setSubject('Reply on Ticket #'.$ticket->id.' — '.$ticket->subject);
            $email->addTo($ticket->submitterEmail, $ticket->submitterName);
            $email->insert('h2')->text('New reply on your ticket');
            $email->insert('p')->text('Ticket #'.$ticket->id.': '.$ticket->subject);
            $email->insert('p')->text($authorName.' wrote:');
            $email->insert('blockquote')->text($content);
            $email->send();
        } catch (\Throwable $e) {
            // Email failure should not block reply creation
        }

        return [$reply];
    }
}
