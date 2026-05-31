<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Http\Request;
use WebFiori\Http\Response;
use WebFiori\Log\FileLogger;

/**
 * First in chain. No dependencies.
 */
class MiddlewareA extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('mw-a');
        $this->setPriority(100);
    }

    public function before(Request $request, Response $response) {
        $this->log('A::before');
    }

    public function after(Request $request, Response $response) {
        $this->log('A::after');
    }

    public function afterSend(Request $request, Response $response) {
        $this->log('A::afterSend');
    }

    private function log(string $msg): void {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs');
        $logger->info($msg);
    }
}
