<?php
namespace App\Ini;

use App\Events\OrderPlacedEvent;
use App\Events\PaymentCompletedEvent;
use App\Listeners\DecrementStockListener;
use App\Listeners\QueuePaymentListener;
use App\Listeners\SendConfirmationListener;
use App\Policies\OrderCancelPolicy;
use App\Policies\OrderViewPolicy;
use App\Services\MockPaymentGateway;
use App\Services\PaymentGatewayInterface;
use WebFiori\Container\ContainerFacade;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Framework\Access;

class Privileges {
    public static function initialize() {
        // RBAC: define roles with permissions
        Access::role('customer', ['orders.create', 'orders.view', 'orders.cancel']);
        Access::role('staff', ['orders.view', 'orders.update', 'orders.ship']);
        Access::role('admin', ['orders.create', 'orders.view', 'orders.cancel', 'orders.update', 'orders.ship', 'orders.manage', 'products.manage']);

        // ABAC: register policy objects
        Access::registerPolicy(new OrderViewPolicy());
        Access::registerPolicy(new OrderCancelPolicy());

        // DI Container: bind payment gateway interface to mock implementation
        ContainerFacade::bind(PaymentGatewayInterface::class, MockPaymentGateway::class);

        // Event Dispatcher: register listeners
        EventDispatcherFacade::listen(OrderPlacedEvent::class, new QueuePaymentListener());
        EventDispatcherFacade::listen(OrderPlacedEvent::class, new DecrementStockListener());
        EventDispatcherFacade::listen(PaymentCompletedEvent::class, new SendConfirmationListener());

        // Health checks auto-discovered from App/Health/ — no manual registration needed
    }
}
