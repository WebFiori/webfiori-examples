<?php
namespace App\Infrastructure;

use App\Domain\PaymentGatewayInterface;

/**
 * Simulates a real payment gateway (production implementation).
 */
class LivePaymentGateway implements PaymentGatewayInterface {
    public function charge(float $amount): string {
        // In a real app, this would call an external API
        return 'txn_' . bin2hex(random_bytes(8));
    }
}
