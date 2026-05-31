<?php
namespace App\Services;

interface BillingProviderInterface {
    /** @return array{success: bool, transactionId: string|null, error: string|null} */
    public function charge(int $tenantId, float $amount): array;
}
