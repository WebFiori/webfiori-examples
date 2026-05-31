<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Http\Request;
use WebFiori\Http\Response;
use WebFiori\Log\FileLogger;

/**
 * Depends on C (which depends on B → A).
 */
class MiddlewareD extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('mw-d');
        $this->setPriority(70);
    }

    public function getDependencies(): array {
        return ['mw-c'];
    }

    public function before(Request $request, Response $response) {
        $this->log('D::before');
    }

    public function after(Request $request, Response $response) {
        $this->log('D::after');
    }

    public function afterSend(Request $request, Response $response) {
        $this->log('D::afterSend');
    }

    private function log(string $msg): void {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs');
        $logger->info($msg);
    }
}
