<?php
namespace App\Health;

use WebFiori\Framework\Health\HealthCheckInterface;
use WebFiori\Framework\Health\HealthCheckResult;

/**
 * Checks that a simulated database connection is alive.
 */
class DatabaseCheck implements HealthCheckInterface {
    public function getName(): string {
        return 'database';
    }

    public function check(): HealthCheckResult {
        // Simulate a database ping. In a real app, you'd run SELECT 1.
        $dbAvailable = file_exists(dirname(__DIR__) . '/Storage/.db-available');

        if ($dbAvailable) {
            return HealthCheckResult::ok(['latency_ms' => 2]);
        }

        return HealthCheckResult::fail('Database unreachable');
    }
}
