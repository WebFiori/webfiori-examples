<?php
namespace Tests;

use App\Events\OrderPlacedEvent;
use App\Events\UserRegisteredEvent;
use App\Listeners\DecrementStockListener;
use App\Listeners\LogOrderListener;
use App\Listeners\SendWelcomeEmailListener;
use PHPUnit\Framework\TestCase;
use WebFiori\Event\EventDispatcher;
use WebFiori\Event\EventDispatcherFacade;

class EventDispatcherTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        EventDispatcherFacade::reset();
    }

    protected function tearDown(): void {
        EventDispatcherFacade::reset();
        parent::tearDown();
    }

    // ========== Event Structure ==========

    public function testOrderPlacedEventProperties() {
        $event = new OrderPlacedEvent(1, 99.99, [['product_id' => 5, 'quantity' => 2]]);
        $this->assertEquals(1, $event->orderId);
        $this->assertEquals(99.99, $event->total);
        $this->assertCount(1, $event->items);
    }

    public function testUserRegisteredEventProperties() {
        $event = new UserRegisteredEvent(7, 'alice@test.com', 'Alice');
        $this->assertEquals(7, $event->userId);
        $this->assertEquals('alice@test.com', $event->email);
        $this->assertEquals('Alice', $event->name);
    }

    // ========== Listener Dispatch ==========

    public function testListenerCalledOnDispatch() {
        $called = false;
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function (OrderPlacedEvent $e) use (&$called) {
            $called = true;
        });

        EventDispatcherFacade::dispatch(new OrderPlacedEvent(1, 50.0, []));
        $this->assertTrue($called);
    }

    public function testMultipleListenersCalled() {
        $count = 0;
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function () use (&$count) { $count++; });
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function () use (&$count) { $count++; });
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function () use (&$count) { $count++; });

        EventDispatcherFacade::dispatch(new OrderPlacedEvent(1, 25.0, []));
        $this->assertEquals(3, $count);
    }

    public function testListenerReceivesEventData() {
        $captured = null;
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function (OrderPlacedEvent $e) use (&$captured) {
            $captured = $e;
        });

        EventDispatcherFacade::dispatch(new OrderPlacedEvent(42, 199.99, [['product_id' => 1, 'quantity' => 3]]));
        $this->assertEquals(42, $captured->orderId);
        $this->assertEquals(199.99, $captured->total);
    }

    public function testDifferentEventsRouteToCorrectListeners() {
        $orderCalled = false;
        $userCalled = false;

        EventDispatcherFacade::listen(OrderPlacedEvent::class, function () use (&$orderCalled) { $orderCalled = true; });
        EventDispatcherFacade::listen(UserRegisteredEvent::class, function () use (&$userCalled) { $userCalled = true; });

        EventDispatcherFacade::dispatch(new UserRegisteredEvent(1, 'a@b.com', 'A'));

        $this->assertFalse($orderCalled);
        $this->assertTrue($userCalled);
    }

    // ========== Class-Based Listeners ==========

    public function testClassBasedListenerHandlesCalled() {
        $listener = new DecrementStockListener();
        EventDispatcherFacade::listen(OrderPlacedEvent::class, $listener);

        $items = [
            ['product_id' => 1, 'quantity' => 2],
            ['product_id' => 3, 'quantity' => 1],
        ];
        EventDispatcherFacade::dispatch(new OrderPlacedEvent(10, 100.0, $items));

        $decremented = $listener->getDecremented();
        $this->assertCount(2, $decremented);
        $this->assertEquals(1, $decremented[0]['product_id']);
        $this->assertEquals(2, $decremented[0]['quantity']);
    }

    public function testLogOrderListenerDoesNotThrow() {
        $listener = new LogOrderListener();
        EventDispatcherFacade::listen(OrderPlacedEvent::class, $listener);

        // Should not throw
        EventDispatcherFacade::dispatch(new OrderPlacedEvent(5, 50.0, []));
        $this->assertTrue(true);
    }

    public function testSendWelcomeEmailListenerDoesNotThrow() {
        $listener = new SendWelcomeEmailListener();
        EventDispatcherFacade::listen(UserRegisteredEvent::class, $listener);

        EventDispatcherFacade::dispatch(new UserRegisteredEvent(1, 'test@test.com', 'Test'));
        $this->assertTrue(true);
    }

    // ========== Dispatcher Instance ==========

    public function testDirectDispatcherInstance() {
        $dispatcher = new EventDispatcher();
        $called = false;

        $dispatcher->listen(OrderPlacedEvent::class, function () use (&$called) { $called = true; });
        $dispatcher->dispatch(new OrderPlacedEvent(1, 10.0, []));

        $this->assertTrue($called);
    }

    public function testGetListenerCount() {
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function () {});
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function () {});
        EventDispatcherFacade::listen(UserRegisteredEvent::class, function () {});

        $this->assertEquals(3, EventDispatcherFacade::getListenerCount());
    }

    public function testGetListenersForEvent() {
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function () {});
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function () {});

        $listeners = EventDispatcherFacade::getListeners(OrderPlacedEvent::class);
        $this->assertCount(2, $listeners);
    }

    public function testResetClearsAllListeners() {
        EventDispatcherFacade::listen(OrderPlacedEvent::class, function () {});
        EventDispatcherFacade::reset();
        $this->assertEquals(0, EventDispatcherFacade::getListenerCount());
    }
}
