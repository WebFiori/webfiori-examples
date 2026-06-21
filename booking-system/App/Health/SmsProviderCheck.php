<?php
namespace App\Health;

use App\Services\NotificationServiceInterface;
use WebFiori\Container\ContainerFacade;
use WebFiori\Framework\Health\HealthCheckInterface;
use WebFiori\Framework\Health\HealthCheckResult;

class SmsProviderCheck implements HealthCheckInterface {
    public function getName(): string {
        return 'sms-provider';
    }

    public function check(): HealthCheckResult {
        try {
            $notifier = ContainerFacade::make(NotificationServiceInterface::class);

            if ($notifier === null) {
                return HealthCheckResult::fail('SMS provider not configured');
            }

            return HealthCheckResult::ok();
        } catch (\Throwable $e) {
            return HealthCheckResult::fail($e->getMessage());
        }
    }
}
