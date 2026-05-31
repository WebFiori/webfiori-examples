<?php
namespace App\Services;

/**
 * Interface for payment gateway implementations.
 */
interface PaymentGatewayInterface {
    /**
     * Charge the given amount.
     *
     * @return array{success: bool, transactionId: string|null, error: string|null}
     */
    public function charge(float $amount, string $currency = 'USD'): array;
}
