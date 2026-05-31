<?php
namespace App\Health;

use WebFiori\Framework\App;
use WebFiori\Framework\Health\HealthCheckInterface;
use WebFiori\Framework\Health\HealthCheckResult;

class DatabaseCheck implements HealthCheckInterface {
    public function getName(): string {
        return 'database';
    }

    public function check(): HealthCheckResult {
        try {
            $conn = App::getConfig()->getDBConnection('billing');

            if ($conn === null) {
                return HealthCheckResult::fail('Connection "billing" not configured');
            }

            $db = new \WebFiori\Database\Database($conn);
            $db->raw("SELECT 1 AS ok")->execute();

            return HealthCheckResult::ok();
        } catch (\Throwable $e) {
            return HealthCheckResult::fail($e->getMessage());
        }
    }
}
