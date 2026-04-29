<?php
namespace App\Commands;

use App\Infrastructure\Repository\ShortLinkRepository;
use WebFiori\Cli\Command;
use WebFiori\Database\Database;
use WebFiori\Framework\App;

/**
 * Lists all short links in a table format.
 */
class ListLinksCommand extends Command {
    public function __construct() {
        parent::__construct('links:list', [], 'Display all short links.');
    }

    public function exec(): int {
        $repo = new ShortLinkRepository(new Database(App::getConfig()->getDBConnection('shortener')));
        $links = $repo->findAll();

        $rows = [];

        foreach ($links as $link) {
            $rows[] = [
                $link->id,
                $link->redirectTo,
                $link->numberOfClicks,
                $link->createdAt ?? '',
                $link->expiresAt ?? 'never',
            ];
        }

        $this->table($rows, ['Code', 'URL', 'Clicks', 'Created', 'Expires']);
        $this->println('Total: '.count($links).' link(s)');

        return 0;
    }
}
