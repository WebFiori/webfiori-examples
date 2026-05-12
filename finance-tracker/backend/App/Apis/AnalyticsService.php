<?php
namespace App\Apis;

use App\Infrastructure\Repository\AccountRepository;
use App\Infrastructure\Repository\TransactionRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

#[RestController('analytics', 'Analytics — aggregated financial data for charts.')]
class AnalyticsService extends WebService {
    /**
     * Returns total income, expenses, and net for a date range.
     * Also returns spending by category and account balances.
     */
    #[GetMapping]
    #[ResponseBody]
    #[RequestParam(name: 'report', type: ParamType::STRING, description: 'Report type: summary, byCategory, monthlyTrend, or accountBalances.')]
    #[RequestParam(name: 'fromDate', type: ParamType::STRING, optional: true, description: 'Start date (Y-m-d).')]
    #[RequestParam(name: 'toDate', type: ParamType::STRING, optional: true, description: 'End date (Y-m-d).')]
    public function getAnalytics(?string $report = null, ?string $fromDate = null, ?string $toDate = null): array {
        $db = new Database(App::getConfig()->getDBConnection('finance'));
        $userId = $this->getUserId();

        return match ($report) {
            'summary' => (new TransactionRepository($db))->summary($userId, $fromDate, $toDate),
            'byCategory' => (new TransactionRepository($db))->spendingByCategory($userId, $fromDate, $toDate),
            'monthlyTrend' => (new TransactionRepository($db))->monthlyTrend($userId),
            'accountBalances' => array_map(fn ($a) => ['name' => $a->name, 'balance' => $a->balance, 'type' => $a->type], (new AccountRepository($db))->findByUserId($userId)),
            default => ['error' => 'Unknown report type. Use: summary, byCategory, monthlyTrend, accountBalances'],
        };
    }
    public function isAuthorized(): bool {
        SessionsManager::start('wf-session');

        return SessionsManager::get('user-id') !== null;
    }

    private function getUserId(): int {
        return (int) SessionsManager::get('user-id');
    }
}
