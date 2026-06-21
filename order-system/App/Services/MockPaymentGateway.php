<?php
namespace App\Services;

/**
 * Mock payment gateway for development/testing.
 *
 * Simulates a successful charge and returns a fake transaction ID.
 * In production, this would be replaced with a real Stripe/PayPal implementation.
 */
class MockPaymentGateway implements PaymentGatewayInterface {
    public function charge(float $amount, string $currency = 'USD'): array {
        // Simulate failure for amounts over 9999
        if ($amount > 9999) {
            return [
                'success' => false,
                'transactionId' => null,
                'error' => 'Amount exceeds limit',
            ];
        }

        return [
            'success' => true,
            'transactionId' => 'txn_' . bin2hex(random_bytes(8)),
            'error' => null,
        ];
    }
}
