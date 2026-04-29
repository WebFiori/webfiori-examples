<?php
namespace App\Commands;

use App\AppCache;
use App\Infrastructure\Repository\ShortLinkRepository;

use WebFiori\Cli\Command;
use WebFiori\Database\Database;
use WebFiori\Framework\App;

/**
 * Removes expired links from DB and purges their cache entries.
 */
class CleanupLinksCommand extends Command {
    public function __construct() {
        parent::__construct('links:cleanup', [], 'Remove expired short links.');
    }

    public function exec(): int {
        $db = new Database(App::getConfig()->getDBConnection('shortener'));

        // Get expired IDs to purge cache
        $now = date('Y-m-d H:i:s');
        $expired = $db->raw('SELECT id FROM short_urls WHERE expires_at <= ?', [$now])->execute()->fetchAll();

        foreach ($expired as $row) {
            AppCache::get()->delete('link:'.$row['id']);
        }

        $repo = new ShortLinkRepository($db);
        $count = $repo->deleteExpired();
        $this->println("Removed $count expired link(s).");

        return 0;
    }
}
