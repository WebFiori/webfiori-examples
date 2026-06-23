<?php
namespace App\Infrastructure;

use App\Domain\NotifierInterface;

/**
 * Discards all notifications — used in tests.
 */
class NullNotifier implements NotifierInterface {
    private array $messages = [];

    public function send(string $message): void {
        $this->messages[] = $message;
    }

    public function getMessages(): array {
        return $this->messages;
    }
}
