<?php
namespace Tests;

use App\Middleware\ApiKeyMiddleware;
use App\Middleware\AuditLogMiddleware;
use App\Middleware\ResponseTimerMiddleware;
use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Middleware\MiddlewareManager;
use WebFiori\Framework\Middleware\RateLimitMiddleware;

class MiddlewareTest extends TestCase {

    public function testAuditLogMiddlewareConstructor(): void {
        $mw = new AuditLogMiddleware();
        $this->assertEquals('audit-log', $mw->getName());
        $this->assertEquals(10, $mw->getPriority());
    }

    public function testApiKeyMiddlewareDependencies(): void {
        $mw = new ApiKeyMiddleware();
        $this->assertEquals('api-key', $mw->getName());
        $this->assertEquals(['audit-log'], $mw->getDependencies());
    }

    public function testResponseTimerDependencies(): void {
        $mw = new ResponseTimerMiddleware();
        $this->assertEquals(['api-key'], $mw->getDependencies());
    }

    public function testTransitiveDependencyChain(): void {
        MiddlewareManager::register(new AuditLogMiddleware());
        MiddlewareManager::register(new ApiKeyMiddleware());
        MiddlewareManager::register(new ResponseTimerMiddleware());

        $timer = MiddlewareManager::getMiddleware('response-timer');
        $this->assertNotNull($timer);

        $apiKey = MiddlewareManager::getMiddleware('api-key');
        $this->assertNotNull($apiKey);
        $this->assertContains('audit-log', $apiKey->getDependencies());
    }

    public function testRateLimitMiddlewareCustomParams(): void {
        $mw = new RateLimitMiddleware(maxRequests: 100, windowSeconds: 120);
        $this->assertEquals('rate-limit', $mw->getName());
    }

    public function testMiddlewareGroups(): void {
        $mw = new AuditLogMiddleware();
        $mw->addToGroup('global');
        $mw->addToGroups(['api', 'logging']);

        $this->assertContains('global', $mw->getGroups());
        $this->assertContains('api', $mw->getGroups());
        $this->assertContains('logging', $mw->getGroups());
    }
}
