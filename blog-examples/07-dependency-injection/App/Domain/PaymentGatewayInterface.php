<?php
namespace App\Domain;

/**
 * Contract for processing payments.
 */
interface PaymentGatewayInterface {
    /**
     * Charge the given amount.
     *
     * @return string Transaction ID.
     */
    public function charge(float $amount): string;
}
