<?php
namespace App\Policies;

use App\Domain\Order;

/**
 * Policy: only pending orders can be cancelled, and only by their owner or admin.
 */
class OrderCancelPolicy {
    public function getPermission(): string {
        return 'orders.cancel';
    }

    public function evaluate($user, ?object $resource = null): bool {
        if (!$resource instanceof Order) {
            return false;
        }

        if ($resource->status !== 'pending') {
            return false;
        }

        if (in_array('admin', $user->getRoles())) {
            return true;
        }

        return $user->getId() === $resource->userId;
    }
}
