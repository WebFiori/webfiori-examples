<?php
namespace App\Services;

use WebFiori\Log\FileLogger;

/**
 * Mock SMS notifier that logs messages to file instead of sending real SMS.
 */
class MockSmsNotifier implements NotificationServiceInterface {
    public function sendSms(string $phone, string $message): bool {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs', 'info');
        $logger->info('SMS sent', ['phone' => $phone, 'message' => $message]);

        return true;
    }
}
