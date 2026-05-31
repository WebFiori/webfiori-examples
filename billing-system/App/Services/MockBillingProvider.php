<?php
namespace App\Services;

class MockBillingProvider implements BillingProviderInterface {
    public function charge(int $tenantId, float $amount): array {
        if ($amount > 5000) {
            return ['success' => false, 'transactionId' => null, 'error' => 'Card declined'];
        }

        return ['success' => true, 'transactionId' => 'txn_' . bin2hex(random_bytes(8)), 'error' => null];
    }
}
