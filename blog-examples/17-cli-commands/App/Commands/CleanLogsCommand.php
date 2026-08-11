<?php
namespace App\Commands;

use WebFiori\Cli\Command;
use WebFiori\Cli\ArgumentOption as Option;

/**
 * Cleans up old log files.
 * Demonstrates argument validation, directory scanning, and exit codes.
 *
 * Usage:
 *   php webfiori logs:clean
 *   php webfiori logs:clean --days=7 --dry-run
 */
class CleanLogsCommand extends Command {
    public function __construct() {
        parent::__construct('logs:clean', [
            '--days' => [
                Option::DESCRIPTION => 'Remove logs older than this many days.',
                Option::OPTIONAL    => true,
                Option::DEFAULT     => '30',
            ],
            '--dry-run' => [
                Option::DESCRIPTION => 'Show what would be deleted without deleting.',
                Option::OPTIONAL    => true,
            ],
        ], 'Clean up old log files from App/Storage/Logs.');
    }

    public function exec(): int {
        $days   = (int) $this->getArgValue('--days');
        $dryRun = $this->getArgValue('--dry-run') !== null;

        if ($days <= 0) {
            $this->error('--days must be a positive integer.');

            return 1;
        }

        $logsDir = APP_PATH . 'Storage' . DIRECTORY_SEPARATOR . 'Logs';

        if (!is_dir($logsDir)) {
            $this->warning("Logs directory not found: $logsDir");

            return 0;
        }

        $threshold = time() - ($days * 86400);
        $files     = glob($logsDir . DIRECTORY_SEPARATOR . '*.log') ?: [];
        $removed   = 0;

        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                if ($dryRun) {
                    $this->println('[dry-run] Would remove: ' . basename($file));
                } else {
                    unlink($file);
                    $this->println('Removed: ' . basename($file));
                }
                $removed++;
            }
        }

        if ($removed === 0) {
            $this->info("No log files older than $days days found.");
        } else {
            $action = $dryRun ? 'Would remove' : 'Removed';
            $this->success("$action $removed log file(s).");
        }

        return 0;
    }
}
