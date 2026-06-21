<?php
namespace App\Policies;

use App\Domain\Order;

/**
 * Policy: customers can only cancel their own pending orders.
 * Admins can cancel any order.
 */
class OrderCancelPolicy {
    public function getPermission(): string {
        return 'orders.cancel';
    }

    public function evaluate($user, ?object $resource = null): bool {
        if ($resource === null) {
            return false;
        }

        if (!$resource instanceof Order) {
            return false;
        }

        // Only pending orders can be cancelled
        if ($resource->status !== Order::STATUS_PENDING) {
            return false;
        }

        // Admin can cancel any pending order
        if (in_array('admin', $user->getRoles())) {
            return true;
        }

        return $user->getId() === $resource->userId;
    }
}
