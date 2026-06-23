<?php
namespace Tests;

use App\Domain\NotifierInterface;
use App\Domain\OrderService;
use App\Domain\PaymentGatewayInterface;
use App\Infrastructure\MockPaymentGateway;
use App\Infrastructure\NullNotifier;
use PHPUnit\Framework\TestCase;
use WebFiori\Container\ContainerFacade;

class OrderServiceTest extends TestCase {

    protected function setUp(): void {
        ContainerFacade::reset();
        ContainerFacade::bind(PaymentGatewayInterface::class, MockPaymentGateway::class);
        ContainerFacade::bind(NotifierInterface::class, NullNotifier::class);
    }

    public function testAutoResolution(): void {
        $service = ContainerFacade::make(OrderService::class);
        $this->assertInstanceOf(OrderService::class, $service);
    }

    public function testProcessPayment(): void {
        $service = ContainerFacade::make(OrderService::class);
        $result = $service->processPayment(99.99, 'ORD-001');

        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertStringStartsWith('mock_txn_', $result['transaction_id']);
        $this->assertEquals('Order ORD-001 processed successfully', $result['message']);
    }

    public function testMockGatewayRecordsCharges(): void {
        $gateway = new MockPaymentGateway();
        ContainerFacade::instance(PaymentGatewayInterface::class, $gateway);

        $service = ContainerFacade::make(OrderService::class);
        $service->processPayment(50.00, 'ORD-002');
        $service->processPayment(75.00, 'ORD-003');

        $this->assertCount(2, $gateway->getCharges());
        $this->assertEquals(50.00, $gateway->getCharges()[0]['amount']);
        $this->assertEquals(75.00, $gateway->getCharges()[1]['amount']);
    }

    public function testNullNotifierCapturesMessages(): void {
        $notifier = new NullNotifier();
        ContainerFacade::instance(NotifierInterface::class, $notifier);

        $service = ContainerFacade::make(OrderService::class);
        $service->processPayment(25.00, 'ORD-004');

        $this->assertCount(1, $notifier->getMessages());
        $this->assertStringContainsString('ORD-004', $notifier->getMessages()[0]);
    }

    public function testSingletonReturnsSameInstance(): void {
        ContainerFacade::reset();
        ContainerFacade::singleton(PaymentGatewayInterface::class, MockPaymentGateway::class);
        ContainerFacade::bind(NotifierInterface::class, NullNotifier::class);

        $a = ContainerFacade::make(PaymentGatewayInterface::class);
        $b = ContainerFacade::make(PaymentGatewayInterface::class);

        $this->assertSame($a, $b);
    }

    public function testTransientReturnsNewInstance(): void {
        $a = ContainerFacade::make(PaymentGatewayInterface::class);
        $b = ContainerFacade::make(PaymentGatewayInterface::class);

        $this->assertNotSame($a, $b);
    }
}
