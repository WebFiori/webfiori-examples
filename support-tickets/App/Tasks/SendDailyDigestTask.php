<?php
namespace App\Tasks;

use App\Infrastructure\Repository\TicketRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\EmailMessage;
use WebFiori\Framework\Scheduler\AbstractTask;
use WebFiori\Mail\Email;
use WebFiori\Mail\SendMode;
use WebFiori\Mail\SMTPAccount;

/**
 * Background task that sends a daily digest email summarizing open tickets.
 *
 * Runs daily at 8:00 AM. Use `--test` argument to store as HTML instead of sending.
 */
class SendDailyDigestTask extends AbstractTask {
    public function __construct() {
        parent::__construct('send-daily-digest', '0 8 * * *', 'Sends daily digest of open tickets.');
        $this->addExecutionArgs([
            '--test' => [
                'description' => 'If provided, store email as HTML file instead of sending via SMTP.',
            ],
        ]);
    }

    public function afterExec(): void {
    }

    public function execute(): void {
        $db = new Database(App::getConfig()->getDBConnection('tickets'));
        $grouped = (new TicketRepository($db))->findOpenGroupedByPriority();

        $total = count($grouped['high']) + count($grouped['medium']) + count($grouped['low']);

        if ($total === 0) {
            return;
        }

        $isTest = $this->getArgValue('--test') !== null;

        if ($isTest) {
            $message = new Email(new SMTPAccount());
            $storePath = APP_PATH.'Storage'.DS.'Logs'.DS.'emails';

            if (!is_dir($storePath)) {
                mkdir($storePath, 0755, true);
            }
            $message->setMode(SendMode::TEST_STORE, ['store-path' => $storePath]);
        } else {
            $message = new EmailMessage('no-reply');
        }

        $message->setSubject('Daily Ticket Digest — '.$total.' open ticket(s)');
        $message->addTo('support@example.com', 'Support Team');

        $message->insert('h2')->text('Daily Ticket Digest');
        $message->insert('p')->text('Date: '.date('Y-m-d').' | Total open: '.$total);

        foreach (['high', 'medium', 'low'] as $priority) {
            $tickets = $grouped[$priority];

            if (empty($tickets)) {
                continue;
            }

            $message->insert('h3')->text(ucfirst($priority).' Priority ('.count($tickets).')');

            foreach ($tickets as $ticket) {
                $message->insert('p')->text(
                    '#'.$ticket->id.' — '.$ticket->subject.
                    ' (from: '.$ticket->submitterEmail.', status: '.$ticket->status.')'
                );
            }
        }

        $message->send();
    }

    public function onFail(): void {
    }

    public function onSuccess(): void {
    }
}
