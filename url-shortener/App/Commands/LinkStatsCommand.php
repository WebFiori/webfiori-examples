<?php
namespace App\Commands;

use App\Infrastructure\Repository\ShortLinkRepository;
use WebFiori\Cli\Command;
use WebFiori\Database\Database;
use WebFiori\Framework\App;

/**
 * Shows link statistics: total links, total clicks, top 10 most clicked.
 */
class LinkStatsCommand extends Command {
    public function __construct() {
        parent::__construct('links:stats', [], 'Show short link statistics.');
    }

    public function exec(): int {
        $repo = new ShortLinkRepository(new Database(App::getConfig()->getDBConnection('shortener')));
        $all = $repo->findAll();
        $totalClicks = array_sum(array_map(fn ($l) => $l->numberOfClicks, $all));

        $this->println('Total links: '.count($all));
        $this->println('Total clicks: '.$totalClicks);

        $top = $repo->topClicked(10);

        if (!empty($top)) {
            $rows = [];

            foreach ($top as $link) {
                $rows[] = [$link->id, $link->redirectTo, $link->numberOfClicks];
            }

            $this->println('');
            $this->table($rows, ['Code', 'URL', 'Clicks']);
        }

        return 0;
    }
}
