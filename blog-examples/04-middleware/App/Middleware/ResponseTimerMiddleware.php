<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Adds response timing header. Depends on api-key (which depends on audit-log).
 * Demonstrates transitive dependency resolution.
 */
class ResponseTimerMiddleware extends AbstractMiddleware {

    private float $startTime;

    public function __construct() {
        parent::__construct('response-timer');
        $this->setPriority(500);
    }

    public function getDependencies(): array {
        return ['api-key']; // api-key depends on audit-log, so full chain: audit-log → api-key → response-timer
    }

    public function before(Request $request, Response $response) {
        $this->startTime = microtime(true);
    }

    public function after(Request $request, Response $response) {
        $duration = round((microtime(true) - $this->startTime) * 1000, 2);
        $response->addHeader('X-Response-Time', $duration . 'ms');
    }

    public function afterSend(Request $request, Response $response) {
    }
}
