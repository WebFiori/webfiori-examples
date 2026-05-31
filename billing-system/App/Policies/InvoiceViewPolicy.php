<?php
namespace App\Policies;

use App\Domain\Invoice;

/**
 * Tenant isolation: users can only view invoices belonging to their tenant.
 * Super-admins can view all.
 */
class InvoiceViewPolicy {
    public function getPermission(): string {
        return 'invoices.view';
    }

    public function evaluate($user, ?object $resource = null): bool {
        if ($resource === null) {
            return true;
        }

        if (!$resource instanceof Invoice) {
            return true;
        }

        if (in_array('super-admin', $user->getRoles())) {
            return true;
        }

        return $user->tenantId === $resource->tenantId;
    }
}
