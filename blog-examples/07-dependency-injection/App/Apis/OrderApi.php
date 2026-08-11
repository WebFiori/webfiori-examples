<?php
namespace App\Apis;

use App\Domain\OrderService;
use WebFiori\Container\ContainerFacade;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

/**
 * Order API — demonstrates DI by resolving OrderService from the container.
 */
#[RestController('orders', 'Order processing API')]
class OrderApi extends WebService {

    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function processOrder(int $orderId, float $amount): array {
        $orderService = ContainerFacade::make(OrderService::class);

        return $orderService->processPayment($orderId, $amount);
    }
}
