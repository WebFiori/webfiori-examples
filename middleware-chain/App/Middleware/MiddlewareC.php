<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Http\Request;
use WebFiori\Http\Response;
use WebFiori\Log\FileLogger;

/**
 * Depends on B (which depends on A).
 */
class MiddlewareC extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('mw-c');
        $this->setPriority(80);
    }

    public function getDependencies(): array {
        return ['mw-b'];
    }

    public function before(Request $request, Response $response) {
        $this->log('C::before');
    }

    public function after(Request $request, Response $response) {
        $this->log('C::after');
    }

    public function afterSend(Request $request, Response $response) {
        $this->log('C::afterSend');
    }

    private function log(string $msg): void {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs');
        $logger->info($msg);
    }
}
