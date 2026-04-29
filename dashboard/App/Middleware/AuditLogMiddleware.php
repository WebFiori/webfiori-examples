<?php
namespace App\Middleware;

use App\Domain\AuditLogEntry;
use App\Infrastructure\Repository\AuditLogRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Logs all write operations (POST/PUT/DELETE) to the audit_log table.
 */
class AuditLogMiddleware extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('audit-log');
        $this->setPriority(50);
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
        $method = $request->getMethod();

        if (!in_array($method, ['POST', 'PUT', 'DELETE'])) {
            return;
        }

        $userId = SessionsManager::get('user-id');
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        try {
            $db = new Database(App::getConfig()->getDBConnection('dashboard'));
            $repo = new AuditLogRepository($db);
            $entry = new AuditLogEntry(
                userId: $userId !== null ? (int) $userId : null,
                action: $method,
                entityType: $this->extractEntityType($uri),
                details: '',
                ipAddress: $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                createdAt: date('Y-m-d H:i:s')
            );
            $repo->save($entry);
        } catch (\Throwable $e) {
            // Audit logging should not block the request
        }
    }

    private function extractEntityType(string $uri): string {
        $parts = explode('/', trim($uri, '/'));

        // e.g. /apis/users -> users, /apis/projects -> projects
        foreach ($parts as $part) {
            if (in_array($part, ['users', 'projects', 'reports', 'auth'])) {
                return $part;
            }
        }

        return 'unknown';
    }
}
