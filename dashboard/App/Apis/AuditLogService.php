<?php
namespace App\Apis;

use App\Infrastructure\Repository\AuditLogRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * Audit log API. Requires VIEW_AUDIT_LOG privilege (Admin only).
 */
#[RestController('audit-log', 'Audit log — view recorded write operations with filters.')]
class AuditLogService extends WebService {
    public function isAuthorized(): bool {
        SessionsManager::start('wf-session');
        $privileges = SessionsManager::get('user-privileges') ?? [];

        return in_array('VIEW_AUDIT_LOG', $privileges);
    }

    /**
     * Returns audit log entries. Supports filtering by user, action type, and date range.
     */
    #[GetMapping]
    #[ResponseBody]
    #[RequestParam(name: 'userId', type: ParamType::INT, optional: true, description: 'Filter by user ID.')]
    #[RequestParam(name: 'actionType', type: ParamType::STRING, optional: true, description: 'Filter by HTTP method: POST, PUT, or DELETE.')]
    #[RequestParam(name: 'fromDate', type: ParamType::STRING, optional: true, description: 'Start date (Y-m-d H:i:s).')]
    #[RequestParam(name: 'toDate', type: ParamType::STRING, optional: true, description: 'End date (Y-m-d H:i:s).')]
    public function getAuditLog(?int $userId = null, ?string $actionType = null, ?string $fromDate = null, ?string $toDate = null): array {
        $db = new Database(App::getConfig()->getDBConnection('dashboard'));

        return (new AuditLogRepository($db))->findFiltered($userId, $actionType, $fromDate, $toDate);
    }
}
