<?php
namespace App\Health;

use WebFiori\Framework\Health\HealthCheckInterface;
use WebFiori\Framework\Health\HealthCheckResult;

/**
 * Checks that an external payment gateway is reachable.
 */
class PaymentGatewayCheck implements HealthCheckInterface {
    public function getName(): string {
        return 'payment-gateway';
    }

    public function check(): HealthCheckResult {
        // Simulate an external service check via a marker file.
        $available = file_exists(dirname(__DIR__) . '/Storage/.payment-available');

        if ($available) {
            return HealthCheckResult::ok(['latency_ms' => 45]);
        }

        return HealthCheckResult::fail('Connection timeout');
    }
}
