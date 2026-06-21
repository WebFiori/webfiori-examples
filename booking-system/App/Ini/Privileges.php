<?php
namespace App\Ini;

use App\Events\AppointmentBookedEvent;
use App\Events\AppointmentCancelledEvent;
use App\Health\DatabaseCheck;
use App\Health\SmsProviderCheck;
use App\Listeners\NotifyOnCancellationListener;
use App\Listeners\NotifyPatientOnBookingListener;
use App\Policies\AppointmentCancelPolicy;
use App\Policies\AppointmentViewPolicy;
use App\Services\MockSmsNotifier;
use App\Services\NotificationServiceInterface;
use WebFiori\Container\ContainerFacade;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Framework\Access;
use WebFiori\Framework\Health\Checks\CacheCheck;
use WebFiori\Framework\Health\HealthCheck;

class Privileges {
    public static function initialize() {
        Access::role('patient', ['appointments.view', 'appointments.create', 'appointments.cancel']);
        Access::role('provider', ['appointments.view', 'appointments.cancel', 'appointments.manage']);
        Access::role('admin', ['appointments.view', 'appointments.create', 'appointments.cancel', 'appointments.manage', 'providers.manage', 'services.manage']);

        Access::registerPolicy(new AppointmentViewPolicy());
        Access::registerPolicy(new AppointmentCancelPolicy());

        ContainerFacade::bind(NotificationServiceInterface::class, MockSmsNotifier::class);

        EventDispatcherFacade::listen(AppointmentBookedEvent::class, new NotifyPatientOnBookingListener());
        EventDispatcherFacade::listen(AppointmentCancelledEvent::class, new NotifyOnCancellationListener());

        HealthCheck::register(new DatabaseCheck());
        HealthCheck::register(new SmsProviderCheck());
        HealthCheck::register(new CacheCheck());
    }
}
