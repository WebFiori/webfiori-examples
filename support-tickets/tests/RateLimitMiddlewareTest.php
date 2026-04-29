<?php
namespace Tests;

use App\Middleware\RateLimitMiddleware;
use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Tests for the rate-limiting middleware.
 */
class RateLimitMiddlewareTest extends TestCase {
    public function testAllowsRequestsUnderLimit() {
        $middleware = new RateLimitMiddleware();
        $request = Request::createFromGlobals();
        $response = new Response();

        for ($i = 0; $i < 5; $i++) {
            $middleware->before($request, $response);
            $this->assertNotEquals(429, $response->getCode(), "Request $i should be allowed");
        }
    }

    public function testBlocksAfterLimit() {
        $middleware = new RateLimitMiddleware();
        $request = Request::createFromGlobals();
        $response = new Response();

        // Exhaust the limit
        for ($i = 0; $i < 5; $i++) {
            $middleware->before($request, $response);
        }

        // 6th request should be blocked
        $response = new Response();
        $middleware->before($request, $response);
        $this->assertEquals(429, $response->getCode());
    }

    public function testGetRequestsAreNotLimited() {
        putenv('REQUEST_METHOD=GET');
        $middleware = new RateLimitMiddleware();
        $request = Request::createFromGlobals();
        $response = new Response();

        for ($i = 0; $i < 10; $i++) {
            $middleware->before($request, $response);
            $this->assertNotEquals(429, $response->getCode());
        }
    }
    protected function setUp(): void {
        parent::setUp();
        putenv('REQUEST_METHOD=POST');
        SessionsManager::start('wf-session');
        // Clear any previous rate limit data
        SessionsManager::set('rate-limit-timestamps', []);
    }

    protected function tearDown(): void {
        SessionsManager::destroy();
        putenv('REQUEST_METHOD=');
        parent::tearDown();
    }
}
