<?php
namespace App\Ini;

use App\Policies\OrderCancelPolicy;
use WebFiori\Framework\Access;

class Privileges {
    public static function initialize(): void {
        Access::role('customer', ['orders.create', 'orders.view', 'orders.cancel']);
        Access::role('admin', ['orders.create', 'orders.view', 'orders.cancel', 'orders.manage']);

        Access::registerPolicy(new OrderCancelPolicy());
    }
}
