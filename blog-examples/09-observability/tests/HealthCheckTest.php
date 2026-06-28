<?php
namespace Tests;

use App\Health\DatabaseCheck;
use App\Health\PaymentGatewayCheck;
use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Health\HealthCheck;
use WebFiori\Framework\Health\HealthCheckResult;

class HealthCheckTest extends TestCase {
    private string $storageDir;

    protected function setUp(): void {
        $this->storageDir = dirname(__DIR__) . '/App/Storage';
        HealthCheck::reset();
    }

    protected function tearDown(): void {
        // Clean up marker files
        @unlink($this->storageDir . '/.db-available');
        @unlink($this->storageDir . '/.payment-available');
        HealthCheck::reset();
    }

    public function testDatabaseCheckPassesWhenAvailable(): void {
        touch($this->storageDir . '/.db-available');

        $check = new DatabaseCheck();
        $result = $check->check();

        $this->assertEquals('ok', $result->getStatus());
        $this->assertNull($result->getReason());
        $this->assertEquals(2, $result->getMeta()['latency_ms']);
    }

    public function testDatabaseCheckFailsWhenUnavailable(): void {
        @unlink($this->storageDir . '/.db-available');

        $check = new DatabaseCheck();
        $result = $check->check();

        $this->assertEquals('fail', $result->getStatus());
        $this->assertEquals('Database unreachable', $result->getReason());
    }

    public function testPaymentGatewayCheckPassesWhenAvailable(): void {
        touch($this->storageDir . '/.payment-available');

        $check = new PaymentGatewayCheck();
        $result = $check->check();

        $this->assertEquals('ok', $result->getStatus());
    }

    public function testPaymentGatewayCheckFailsWhenUnavailable(): void {
        @unlink($this->storageDir . '/.payment-available');

        $check = new PaymentGatewayCheck();
        $result = $check->check();

        $this->assertEquals('fail', $result->getStatus());
        $this->assertEquals('Connection timeout', $result->getReason());
    }

    public function testRunAllReturnsAggregateOk(): void {
        touch($this->storageDir . '/.db-available');
        touch($this->storageDir . '/.payment-available');

        HealthCheck::register(new DatabaseCheck());
        HealthCheck::register(new PaymentGatewayCheck());

        $result = HealthCheck::runAll();

        $this->assertEquals('ok', $result['status']);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertEquals('ok', $result['checks']['database']['status']);
        $this->assertEquals('ok', $result['checks']['payment-gateway']['status']);
    }

    public function testRunAllReturnsFailWhenOneCheckFails(): void {
        touch($this->storageDir . '/.db-available');
        @unlink($this->storageDir . '/.payment-available');

        HealthCheck::register(new DatabaseCheck());
        HealthCheck::register(new PaymentGatewayCheck());

        $result = HealthCheck::runAll();

        $this->assertEquals('fail', $result['status']);
        $this->assertEquals('ok', $result['checks']['database']['status']);
        $this->assertEquals('fail', $result['checks']['payment-gateway']['status']);
    }

    public function testCallableCheck(): void {
        HealthCheck::register('custom', function () {
            return HealthCheckResult::ok(['info' => 'all good']);
        });

        $result = HealthCheck::runAll();

        $this->assertEquals('ok', $result['status']);
        $this->assertEquals('ok', $result['checks']['custom']['status']);
    }

    public function testAfterAllCallback(): void {
        $captured = null;

        HealthCheck::register('always-ok', function () {
            return HealthCheckResult::ok();
        });

        HealthCheck::afterAll(function (array $result) use (&$captured) {
            $captured = $result;
        });

        HealthCheck::runAll();

        $this->assertNotNull($captured);
        $this->assertEquals('ok', $captured['status']);
    }
}
