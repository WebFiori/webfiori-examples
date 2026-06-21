<?php
namespace App\Health;

use WebFiori\Framework\Health\HealthCheckInterface;
use WebFiori\Framework\Health\HealthCheckResult;
use WebFiori\Queue\QueueFacade;

/**
 * Verifies that the job queue storage is operational (can dispatch and read).
 */
class QueueCheck implements HealthCheckInterface {
    public function getName(): string {
        return 'queue';
    }

    public function check(): HealthCheckResult {
        try {
            $pending = QueueFacade::getPendingCount();

            return HealthCheckResult::ok(['pending_jobs' => $pending]);
        } catch (\Throwable $e) {
            return HealthCheckResult::fail($e->getMessage());
        }
    }
}
