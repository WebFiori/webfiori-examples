<?php
namespace App\Apis;

use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;
use WebFiori\Log\LoggerFacade;

/**
 * A simple order API that demonstrates logging in action.
 */
#[RestController('orders', 'Order API with logging')]
class OrderService extends WebService {

    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'product', type: ParamType::STRING)]
    #[RequestParam(name: 'quantity', type: ParamType::INT)]
    public function createOrder(string $product, int $quantity): array {
        $orderId = random_int(1000, 9999);

        LoggerFacade::info('Order created', [
            'order_id' => $orderId,
            'product' => $product,
            'quantity' => $quantity,
        ]);

        return [
            'order_id' => $orderId,
            'product' => $product,
            'quantity' => $quantity,
            'status' => 'created',
        ];
    }
}
