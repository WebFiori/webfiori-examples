<?php
namespace App\Health;

use App\Services\BillingProviderInterface;
use WebFiori\Container\ContainerFacade;
use WebFiori\Framework\Health\HealthCheckInterface;
use WebFiori\Framework\Health\HealthCheckResult;

class BillingProviderCheck implements HealthCheckInterface {
    public function getName(): string {
        return 'billing-provider';
    }

    public function check(): HealthCheckResult {
        try {
            ContainerFacade::make(BillingProviderInterface::class);

            return HealthCheckResult::ok();
        } catch (\Throwable $e) {
            return HealthCheckResult::fail($e->getMessage());
        }
    }
}
