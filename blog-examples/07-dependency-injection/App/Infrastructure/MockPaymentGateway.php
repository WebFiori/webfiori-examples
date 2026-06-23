<?php
namespace App\Infrastructure;

use App\Domain\PaymentGatewayInterface;

/**
 * In-memory payment gateway for testing — no external calls.
 */
class MockPaymentGateway implements PaymentGatewayInterface {
    private array $charges = [];

    public function charge(float $amount): string {
        $id = 'mock_txn_' . count($this->charges) + 1;
        $this->charges[] = ['amount' => $amount, 'id' => $id];
        return $id;
    }

    public function getCharges(): array {
        return $this->charges;
    }
}
