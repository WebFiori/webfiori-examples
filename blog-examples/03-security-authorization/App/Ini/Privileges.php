<?php
namespace App\Ini;

use App\Policies\OrderCancelPolicy;
use WebFiori\Framework\Access;

class Privileges {
    public static function initialize(): void {
        Access::role('customer', ['orders_create', 'orders_view', 'orders_cancel']);
        Access::role('admin', ['orders_create', 'orders_view', 'orders_cancel', 'orders_manage']);

        Access::registerPolicy(new OrderCancelPolicy());
    }
}
