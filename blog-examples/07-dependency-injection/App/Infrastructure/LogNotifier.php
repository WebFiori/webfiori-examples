<?php
namespace App\Infrastructure;

use App\Domain\NotifierInterface;

/**
 * Logs notifications to a file (production implementation).
 */
class LogNotifier implements NotifierInterface {
    public function send(string $message): void {
        $logFile = dirname(__DIR__, 2) . '/App/Storage/notifications.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " $message\n", FILE_APPEND);
    }
}
