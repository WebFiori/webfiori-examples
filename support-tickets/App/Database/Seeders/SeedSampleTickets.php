<?php
namespace App\Database\Seeders;

use App\Database\Migrations\CreateTicketTables;
use App\Domain\Reply;
use App\Domain\Ticket;
use App\Infrastructure\Repository\ReplyRepository;
use App\Infrastructure\Repository\TicketRepository;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractSeeder;

/**
 * Seeds sample tickets and replies for development.
 */
class SeedSampleTickets extends AbstractSeeder {
    public function getDependencies(): array {
        return [CreateTicketTables::class];
    }
    public function getEnvironments(): array {
        return ['dev', 'test'];
    }

    public function run(Database $db): void {
        $now = date('Y-m-d H:i:s');
        $ticketRepo = new TicketRepository($db);
        $replyRepo = new ReplyRepository($db);

        $tickets = [
            new Ticket(subject: 'Cannot login', description: 'I get an error when trying to login.', submitterName: 'Alice', submitterEmail: 'alice@example.com', priority: 'high', createdAt: $now),
            new Ticket(subject: 'Feature request: dark mode', description: 'Please add dark mode support.', submitterName: 'Bob', submitterEmail: 'bob@example.com', priority: 'low', createdAt: $now),
            new Ticket(subject: 'Billing issue', description: 'I was charged twice this month.', submitterName: 'Charlie', submitterEmail: 'charlie@example.com', priority: 'high', status: 'in-progress', createdAt: $now),
        ];

        foreach ($tickets as $t) {
            $ticketRepo->save($t);
        }

        $replies = [
            new Reply(ticketId: 1, authorName: 'Support', content: 'Can you share a screenshot of the error?', createdAt: $now),
            new Reply(ticketId: 3, authorName: 'Support', content: 'We are looking into this. Will update soon.', createdAt: $now),
        ];

        foreach ($replies as $r) {
            $replyRepo->save($r);
        }
    }
}
