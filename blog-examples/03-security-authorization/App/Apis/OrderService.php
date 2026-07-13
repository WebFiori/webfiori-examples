<?php
namespace App\Apis;

use App\Domain\Order;
use WebFiori\Framework\Access;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\DeleteMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\PreAuthorize;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\RequiresAuth;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\ForbiddenException;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\SecurityContext;
use WebFiori\Http\WebService;

/**
 * Order API demonstrating RBAC and ABAC authorization.
 */
#[RestController('orders', 'Order API with security')]
#[RequiresAuth]
class OrderService extends WebService {

    /** @var Order[] In-memory order store for demo purposes */
    private static array $orders = [];
    private static int $nextId = 1;

    public function isAuthorized(): bool {
        return SecurityContext::isAuthenticated();
    }

    #[GetMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAuthority('orders.view')")]
    public function listOrders(): array {
        $user = SecurityContext::getCurrentUser();

        if (in_array('admin', $user->getRoles())) {
            return ['orders' => self::$orders];
        }

        $myOrders = array_filter(self::$orders, fn($o) => $o->userId === $user->getId());
        return ['orders' => array_values($myOrders)];
    }

    #[PostMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAuthority('orders.create')")]
    #[RequestParam(name: 'total', type: ParamType::DOUBLE, description: 'Order total')]
    public function createOrder(): array {
        $user = SecurityContext::getCurrentUser();
        $order = new Order(self::$nextId++, $user->getId(), $this->getParamVal('total'));
        self::$orders[$order->id] = $order;

        return ['message' => 'Order created', 'order' => (array) $order];
    }

    #[DeleteMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAuthority('orders.cancel')")]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Order ID')]
    public function cancelOrder(): array {
        $id = $this->getParamVal('id');
        $order = self::$orders[$id] ?? null;

        if ($order === null) {
            throw new NotFoundException('Order not found.');
        }

        $user = SecurityContext::getCurrentUser();

        if (!Access::can($user, 'orders.cancel', $order)) {
            throw new ForbiddenException('You cannot cancel this order.');
        }

        $order->status = 'cancelled';
        return ['message' => 'Order cancelled', 'order' => (array) $order];
    }

    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'public', type: ParamType::STRING, optional: true)]
    public function health(): array {
        return ['status' => 'ok'];
    }
}
