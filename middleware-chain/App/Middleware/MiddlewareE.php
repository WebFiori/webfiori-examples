<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Http\Request;
use WebFiori\Http\Response;
use WebFiori\Log\FileLogger;

/**
 * Last in chain. Depends on D (which depends on C → B → A).
 * Only this middleware is registered on the route — the framework
 * should auto-resolve the full dependency chain.
 */
class MiddlewareE extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('mw-e');
        $this->setPriority(60);
    }

    public function getDependencies(): array {
        return ['mw-d'];
    }

    public function before(Request $request, Response $response) {
        $this->log('E::before');
    }

    public function after(Request $request, Response $response) {
        $this->log('E::after');
    }

    public function afterSend(Request $request, Response $response) {
        $this->log('E::afterSend');
    }

    private function log(string $msg): void {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs');
        $logger->info($msg);
    }
}
