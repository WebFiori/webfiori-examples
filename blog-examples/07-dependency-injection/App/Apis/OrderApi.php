<?php
namespace App\Apis;

use App\Domain\OrderService;
use WebFiori\Container\ContainerFacade;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * Order API — demonstrates DI by resolving OrderService from the container.
 */
#[RestController('orders', 'Order processing API')]
class OrderApi extends WebService {

    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'order-id', type: ParamType::STRING, description: 'Order ID')]
    #[RequestParam(name: 'amount', type: ParamType::DOUBLE, description: 'Payment amount')]
    public function processOrder(): array {
        $orderService = ContainerFacade::make(OrderService::class);

        return $orderService->processPayment(
            $this->getParamVal('amount'),
            $this->getParamVal('order-id')
        );
    }
}
