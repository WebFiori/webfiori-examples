<?php
namespace App\Ini;

use App\Domain\NotifierInterface;
use App\Domain\PaymentGatewayInterface;
use App\Infrastructure\LivePaymentGateway;
use App\Infrastructure\LogNotifier;
use App\Infrastructure\MockPaymentGateway;
use App\Infrastructure\NullNotifier;
use WebFiori\Container\ContainerFacade;

class Privileges {
    public static function initialize(): void {
        if (getenv('APP_ENV') === 'testing') {
            ContainerFacade::bind(PaymentGatewayInterface::class, MockPaymentGateway::class);
            ContainerFacade::bind(NotifierInterface::class, NullNotifier::class);
        } else {
            ContainerFacade::bind(PaymentGatewayInterface::class, LivePaymentGateway::class);
            ContainerFacade::bind(NotifierInterface::class, LogNotifier::class);
        }
    }
}
