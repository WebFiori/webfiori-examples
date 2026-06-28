<?php
namespace App\Jobs;

use WebFiori\Log\LoggerFacade;
use WebFiori\Queue\Job;

/**
 * Simulates sending a welcome email to a new user.
 */
class SendWelcomeEmailJob implements Job {
    public function __construct(
        private string $email,
        private string $name
    ) {}

    public function getMaxAttempts(): int {
        return 3;
    }

    public function getRetryDelaySeconds(): int {
        return 30;
    }

    public function handle(): void {
        LoggerFacade::info('Welcome email sent', [
            'email' => $this->email,
            'name' => $this->name,
        ]);
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getName(): string {
        return $this->name;
    }
}
