<?php
namespace Tests;

use App\Database\Migrations\CreateOrderTables;
use App\Database\Seeders\SeedSampleData;
use App\Domain\Order;
use App\Domain\OrderItem;
use App\Infrastructure\Repository\OrderItemRepository;
use App\Infrastructure\Repository\OrderRepository;
use App\Infrastructure\Repository\ProductRepository;
use App\Infrastructure\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Database;

/**
 * Integration tests using SQLite for repositories and API logic.
 */
class IntegrationTest extends TestCase {
    private static ?Database $db = null;

    public static function setUpBeforeClass(): void {
        // Create in-memory SQLite DB
        $conn = new ConnectionInfo('sqlite', '', '', ':memory:');
        self::$db = new Database($conn);

        // Run migrations
        $migration = new CreateOrderTables();
        $migration->up(self::$db);

        // Seed data
        $seeder = new SeedSampleData();
        $seeder->up(self::$db);
    }

    private function db(): Database {
        return self::$db;
    }

    // ========== User Repository ==========

    public function testFindUserByEmail() {
        $repo = new UserRepository($this->db());
        $user = $repo->findByEmail('admin@example.com');
        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->role);
        $this->assertEquals('Admin User', $user->name);
    }

    public function testFindUserByEmailReturnsNullForUnknown() {
        $repo = new UserRepository($this->db());
        $user = $repo->findByEmail('nobody@example.com');
        $this->assertNull($user);
    }

    public function testFindUserById() {
        $repo = new UserRepository($this->db());
        $user = $repo->findById(1);
        $this->assertNotNull($user);
        $this->assertEquals('Admin User', $user->name);
    }

    public function testUserPasswordVerifies() {
        $repo = new UserRepository($this->db());
        $user = $repo->findByEmail('john@example.com');
        $this->assertTrue(password_verify('john123', $user->passwordHash));
        $this->assertFalse(password_verify('wrong', $user->passwordHash));
    }

    // ========== Product Repository ==========

    public function testFindAllProducts() {
        $repo = new ProductRepository($this->db());
        $products = $repo->findAll();
        $this->assertCount(5, $products);
    }

    public function testFindProductById() {
        $repo = new ProductRepository($this->db());
        $product = $repo->findById(1);
        $this->assertNotNull($product);
        $this->assertEquals('Wireless Keyboard', $product->name);
        $this->assertEquals(79.99, $product->price);
    }

    public function testDecrementStock() {
        $repo = new ProductRepository($this->db());
        $before = $repo->findById(2);
        $originalStock = $before->stock;

        $repo->decrementStock(2, 3);

        $after = $repo->findById(2);
        $this->assertEquals($originalStock - 3, $after->stock);
    }

    // ========== Order Repository ==========

    public function testCreateAndFindOrder() {
        $repo = new OrderRepository($this->db());

        $order = new Order(
            userId: 3,
            status: Order::STATUS_PENDING,
            total: 159.98,
            createdAt: date('Y-m-d H:i:s')
        );
        $repo->save($order);

        // Find by user
        $orders = $repo->findByUserId(3);
        $this->assertNotEmpty($orders);

        $found = end($orders);
        $this->assertEquals(3, $found->userId);
        $this->assertEquals(Order::STATUS_PENDING, $found->status);
        $this->assertEquals(159.98, $found->total);
    }

    public function testUpdateOrderStatus() {
        $repo = new OrderRepository($this->db());

        // Create order
        $order = new Order(userId: 3, status: Order::STATUS_PENDING, total: 50.0, createdAt: date('Y-m-d H:i:s'));
        $repo->save($order);

        // Get it back
        $orders = $repo->findByUserId(3);
        $lastOrder = end($orders);

        // Update
        $lastOrder->status = Order::STATUS_PAID;
        $lastOrder->updatedAt = date('Y-m-d H:i:s');
        $repo->save($lastOrder);

        // Verify
        $updated = $repo->findById($lastOrder->id);
        $this->assertEquals(Order::STATUS_PAID, $updated->status);
    }

    // ========== Order Item Repository ==========

    public function testSaveAndFindOrderItems() {
        $orderRepo = new OrderRepository($this->db());

        // Create an order first
        $order = new Order(userId: 3, status: 'pending', total: 129.98, createdAt: date('Y-m-d H:i:s'));
        $orderRepo->save($order);
        $orders = $orderRepo->findByUserId(3);
        $savedOrder = end($orders);

        $itemRepo = new OrderItemRepository($this->db());
        $item1 = new OrderItem(orderId: $savedOrder->id, productId: 1, quantity: 1, unitPrice: 79.99);
        $item2 = new OrderItem(orderId: $savedOrder->id, productId: 2, quantity: 1, unitPrice: 49.99);
        $itemRepo->save($item1);
        $itemRepo->save($item2);

        $items = $itemRepo->findByOrderId($savedOrder->id);
        $this->assertCount(2, $items);
        $this->assertEquals(79.99, $items[0]->unitPrice);
    }

    // ========== Auth Logic ==========

    public function testLoginVerifiesCredentials() {
        $repo = new UserRepository($this->db());
        $user = $repo->findByEmail('admin@example.com');

        $this->assertNotNull($user);
        $this->assertTrue(password_verify('admin123', $user->passwordHash));
        $this->assertEquals('admin', $user->role);
    }

    public function testLoginRejectsWrongPassword() {
        $repo = new UserRepository($this->db());
        $user = $repo->findByEmail('admin@example.com');
        $this->assertFalse(password_verify('wrongpassword', $user->passwordHash));
    }

    // ========== Full Order Placement Flow ==========

    public function testPlaceOrderFlow() {
        $productRepo = new ProductRepository($this->db());
        $orderRepo = new OrderRepository($this->db());
        $itemRepo = new OrderItemRepository($this->db());

        // Simulate order placement logic from OrderService
        $product = $productRepo->findById(1);
        $this->assertNotNull($product);
        $this->assertGreaterThanOrEqual(2, $product->stock);

        $quantity = 2;
        $total = $product->price * $quantity;

        // Create order
        $order = new Order(userId: 4, status: Order::STATUS_PENDING, total: $total, createdAt: date('Y-m-d H:i:s'));
        $orderRepo->save($order);

        $orders = $orderRepo->findByUserId(4);
        $savedOrder = end($orders);

        // Create item
        $item = new OrderItem(orderId: $savedOrder->id, productId: $product->id, quantity: $quantity, unitPrice: $product->price);
        $itemRepo->save($item);

        // Decrement stock
        $productRepo->decrementStock($product->id, $quantity);

        // Verify
        $updatedProduct = $productRepo->findById(1);
        $this->assertEquals($product->stock - $quantity, $updatedProduct->stock);

        $items = $itemRepo->findByOrderId($savedOrder->id);
        $this->assertCount(1, $items);
        $this->assertEquals($quantity, $items[0]->quantity);
    }
}
