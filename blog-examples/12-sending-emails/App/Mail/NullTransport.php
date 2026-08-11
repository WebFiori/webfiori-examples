<?php
namespace App\Mail;

use WebFiori\Mail\Email;
use WebFiori\Mail\TransportInterface;

/**
 * Captures sent emails in memory instead of delivering them.
 * Use this in tests to assert on email content without hitting an SMTP server.
 */
class NullTransport implements TransportInterface {
    private array $sent = [];

    public function getName(): string {
        return 'null';
    }

    public function send(Email $message): void {
        $this->sent[] = $message;
    }

    /**
     * @return Email[]
     */
    public function getSentMessages(): array {
        return $this->sent;
    }

    public function getLastMessage(): ?Email {
        return empty($this->sent) ? null : $this->sent[count($this->sent) - 1];
    }

    public function reset(): void {
        $this->sent = [];
    }
}
