<?php
namespace App\Ini;

use App\Events\InvoiceCreatedEvent;
use App\Events\PaymentFailedEvent;
use App\Health\BillingProviderCheck;
use App\Health\DatabaseCheck;
use App\Listeners\LogPaymentFailureListener;
use App\Listeners\QueueInvoicePaymentListener;
use App\Policies\InvoiceViewPolicy;
use App\Services\BillingProviderInterface;
use App\Services\MockBillingProvider;
use WebFiori\Container\ContainerFacade;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Framework\Access;
use WebFiori\Framework\Health\Checks\CacheCheck;
use WebFiori\Framework\Health\HealthCheck;

class Privileges {
    public static function initialize() {
        Access::role('super-admin', ['tenants.manage', 'subscriptions.manage', 'invoices.view', 'invoices.generate', 'usage.view']);
        Access::role('tenant-admin', ['subscriptions.view', 'invoices.view', 'usage.view', 'members.manage']);
        Access::role('member', ['invoices.view', 'usage.view']);

        Access::registerPolicy(new InvoiceViewPolicy());

        ContainerFacade::bind(BillingProviderInterface::class, MockBillingProvider::class);

        EventDispatcherFacade::listen(InvoiceCreatedEvent::class, new QueueInvoicePaymentListener());
        EventDispatcherFacade::listen(PaymentFailedEvent::class, new LogPaymentFailureListener());

        HealthCheck::register(new DatabaseCheck());
        HealthCheck::register(new BillingProviderCheck());
        HealthCheck::register(new CacheCheck());
    }
}
