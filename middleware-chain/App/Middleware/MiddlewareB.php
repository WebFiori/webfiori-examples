<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Http\Request;
use WebFiori\Http\Response;
use WebFiori\Log\FileLogger;

/**
 * Depends on A.
 */
class MiddlewareB extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('mw-b');
        $this->setPriority(90);
    }

    public function getDependencies(): array {
        return ['mw-a'];
    }

    public function before(Request $request, Response $response) {
        $this->log('B::before');
    }

    public function after(Request $request, Response $response) {
        $this->log('B::after');
    }

    public function afterSend(Request $request, Response $response) {
        $this->log('B::afterSend');
    }

    private function log(string $msg): void {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs');
        $logger->info($msg);
    }
}
