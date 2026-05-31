<?php
namespace App\Apis;

use App\Domain\Order;
use App\Domain\OrderItem;
use App\Events\OrderPlacedEvent;
use App\Infrastructure\Repository\OrderItemRepository;
use App\Infrastructure\Repository\OrderRepository;
use App\Infrastructure\Repository\ProductRepository;
use WebFiori\Database\Database;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Framework\Access;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\DeleteMapping;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\PreAuthorize;
use WebFiori\Http\Annotations\PutMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\RequiresAuth;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\BadRequestException;
use WebFiori\Http\Exceptions\ForbiddenException;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\SecurityContext;
use WebFiori\Http\WebService;

/**
 * Order management API.
 *
 * All endpoints require authentication. ABAC policies enforce ownership rules.
 */
#[RestController('orders', 'Order management API')]
#[RequiresAuth]
class OrderService extends WebService {
    public function isAuthorized(): bool {
        return SecurityContext::isAuthenticated();
    }
    /**
     * List orders for the current user, or all orders for staff/admin.
     */
    #[GetMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAnyAuthority('orders.view', 'orders.manage')")]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true, description: 'Order ID')]
    public function getOrders(?int $id = null): array {
        $db = $this->getDb();
        $repo = new OrderRepository($db);
        $user = SecurityContext::getCurrentUser();

        if ($id !== null) {
            $order = $repo->findById($id);

            if ($order === null) {
                throw new NotFoundException('Order not found.');
            }

            if (!Access::can($user, 'orders.view', $order)) {
                throw new ForbiddenException('You cannot view this order.');
            }

            $items = (new OrderItemRepository($db))->findByOrderId($order->id);

            return ['order' => $order, 'items' => $items];
        }

        // Admin/staff see all, customers see own
        if (in_array($user->getRoles()[0] ?? '', ['admin', 'staff'])) {
            return $repo->findAll();
        }

        return $repo->findByUserId($user->getId());
    }

    /**
     * Place a new order with items.
     */
    #[PostMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAuthority('orders.create')")]
    #[RequestParam(name: 'items', type: ParamType::STRING, description: 'JSON array of {productId, quantity}')]
    public function createOrder(?string $items = null): array {
        $itemsData = json_decode($items, true);

        if (!is_array($itemsData) || empty($itemsData)) {
            throw new BadRequestException('Items must be a non-empty JSON array.');
        }

        $db = $this->getDb();
        $productRepo = new ProductRepository($db);
        $user = SecurityContext::getCurrentUser();

        // Validate products and calculate total
        $total = 0.0;
        $orderItems = [];

        foreach ($itemsData as $item) {
            if (!isset($item['productId'], $item['quantity'])) {
                throw new BadRequestException('Each item must have productId and quantity.');
            }

            $product = $productRepo->findById((int) $item['productId']);

            if ($product === null) {
                throw new NotFoundException('Product #' . $item['productId'] . ' not found.');
            }

            if ($product->stock < (int) $item['quantity']) {
                throw new BadRequestException('Insufficient stock for ' . $product->name);
            }

            $orderItems[] = new OrderItem(
                productId: $product->id,
                quantity: (int) $item['quantity'],
                unitPrice: $product->price
            );

            $total += $product->price * (int) $item['quantity'];
        }

        // Create order
        $order = new Order(
            userId: $user->getId(),
            status: Order::STATUS_PENDING,
            total: $total,
            createdAt: date('Y-m-d H:i:s')
        );

        $orderRepo = new OrderRepository($db);
        $orderRepo->save($order);

        // Get created order ID
        $result = $db->table('orders')->select()
            ->where('user-id', $user->getId())
            ->execute();
        $rows = $result->fetchAll();
        $order->id = !empty($rows) ? (int) end($rows)['id'] : null;

        // Save items
        $itemRepo = new OrderItemRepository($db);

        foreach ($orderItems as $oi) {
            $oi->orderId = $order->id;
            $itemRepo->save($oi);
        }

        // Dispatch event (triggers payment queue + stock decrement)
        EventDispatcherFacade::dispatch(new OrderPlacedEvent($order, $orderItems));

        return ['order' => $order, 'items' => $orderItems];
    }

    /**
     * Cancel an order (ABAC: only own pending orders, or admin).
     */
    #[DeleteMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAuthority('orders.cancel')")]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Order ID')]
    public function cancelOrder(?int $id = null): array {
        $db = $this->getDb();
        $repo = new OrderRepository($db);
        $order = $repo->findById($id);

        if ($order === null) {
            throw new NotFoundException('Order not found.');
        }

        $user = SecurityContext::getCurrentUser();

        if (!Access::can($user, 'orders.cancel', $order)) {
            throw new ForbiddenException('You cannot cancel this order.');
        }

        $order->status = Order::STATUS_CANCELLED;
        $order->updatedAt = date('Y-m-d H:i:s');
        $repo->save($order);

        return [$order];
    }

    /**
     * Ship an order (staff/admin only).
     */
    #[PutMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAuthority('orders.ship')")]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Order ID')]
    public function shipOrder(?int $id = null): array {
        $db = $this->getDb();
        $repo = new OrderRepository($db);
        $order = $repo->findById($id);

        if ($order === null) {
            throw new NotFoundException('Order not found.');
        }

        if ($order->status !== Order::STATUS_PAID) {
            throw new BadRequestException('Only paid orders can be shipped.');
        }

        $order->status = Order::STATUS_SHIPPED;
        $order->updatedAt = date('Y-m-d H:i:s');
        $repo->save($order);

        return [$order];
    }

    private function getDb(): Database {
        return new Database(App::getConfig()->getDBConnection('orders'));
    }
}
