<?php
namespace App\Policies;

use App\Domain\Order;

/**
 * Policy: customers can only view their own orders.
 * Admins and staff can view any order (no policy restriction for them since
 * the RBAC check already gates by role).
 */
class OrderViewPolicy {
    public function getPermission(): string {
        return 'orders.view';
    }

    public function evaluate($user, ?object $resource = null): bool {
        if ($resource === null) {
            return true;
        }

        if (!$resource instanceof Order) {
            return true;
        }

        // Staff and admin can view any order
        if (in_array($user->getRoles()[0] ?? '', ['admin', 'staff'])) {
            return true;
        }

        return $user->getId() === $resource->userId;
    }
}
