<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Logs every request — method, path, and response code.
 */
class AuditLogMiddleware extends AbstractMiddleware {

    private static array $log = [];

    public function __construct() {
        parent::__construct('audit-log');
        $this->setPriority(10); // low priority — runs after security middleware
    }

    public function before(Request $request, Response $response) {
        self::$log[] = [
            'time' => date('Y-m-d H:i:s'),
            'method' => $request->getMethod(),
            'path' => $request->getRequestedURI(),
        ];
    }

    public function after(Request $request, Response $response) {
        if (!empty(self::$log)) {
            $last = &self::$log[count(self::$log) - 1];
            $last['status'] = $response->getCode();
        }
    }

    public function afterSend(Request $request, Response $response) {
        // In production, persist to file or database here
    }

    public static function getLog(): array {
        return self::$log;
    }

    public static function clearLog(): void {
        self::$log = [];
    }
}
