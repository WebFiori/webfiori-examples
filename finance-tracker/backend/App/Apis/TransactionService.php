<?php
namespace App\Apis;

use App\Domain\Transaction;
use App\Infrastructure\Repository\AccountRepository;
use App\Infrastructure\Repository\TransactionRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Annotations\DeleteMapping;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

#[RestController('transactions', 'Transactions — manage income and expense transactions.')]
class TransactionService extends WebService {
    #[PostMapping]
    #[ResponseBody]
    #[RequestParam(name: 'accountId', type: ParamType::INT, description: 'Account ID.')]
    #[RequestParam(name: 'categoryId', type: ParamType::INT, optional: true, description: 'Category ID.')]
    #[RequestParam(name: 'type', type: ParamType::STRING, description: 'Type: income or expense.')]
    #[RequestParam(name: 'amount', type: ParamType::DOUBLE, description: 'Amount (positive number).')]
    #[RequestParam(name: 'description', type: ParamType::STRING, optional: true, default: '', description: 'Description.')]
    #[RequestParam(name: 'date', type: ParamType::STRING, description: 'Transaction date (Y-m-d).')]
    public function createTransaction(?int $accountId = null, ?int $categoryId = null, ?string $type = null, ?float $amount = null, ?string $description = null, ?string $date = null): array {
        // Verify account belongs to user
        $db = new Database(App::getConfig()->getDBConnection('finance'));
        $account = (new AccountRepository($db))->findById($accountId);

        if ($account === null || $account->userId !== $this->getUserId()) {
            throw new NotFoundException('Account not found.');
        }

        $tx = new Transaction(
            accountId: $accountId,
            categoryId: $categoryId,
            type: $type,
            amount: $amount,
            description: $description ?? '',
            date: $date,
            createdAt: date('Y-m-d H:i:s')
        );

        (new TransactionRepository($db))->save($tx);

        // Update account balance
        $delta = $type === 'income' ? $amount : -$amount;
        $account->balance += $delta;
        (new AccountRepository($db))->save($account);

        return [$tx];
    }

    #[DeleteMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Transaction ID.')]
    public function deleteTransaction(?int $id = null): array {
        $repo = $this->getRepo();
        $tx = $repo->findById($id);

        if ($tx === null) {
            throw new NotFoundException('Transaction not found.');
        }

        $repo->deleteById($id);

        return [$tx];
    }

    #[GetMapping]
    #[ResponseBody]
    #[RequestParam(name: 'accountId', type: ParamType::INT, optional: true, description: 'Filter by account.')]
    #[RequestParam(name: 'categoryId', type: ParamType::INT, optional: true, description: 'Filter by category.')]
    #[RequestParam(name: 'type', type: ParamType::STRING, optional: true, description: 'Filter: income or expense.')]
    #[RequestParam(name: 'fromDate', type: ParamType::STRING, optional: true, description: 'Start date (Y-m-d).')]
    #[RequestParam(name: 'toDate', type: ParamType::STRING, optional: true, description: 'End date (Y-m-d).')]
    public function getTransactions(?int $accountId = null, ?int $categoryId = null, ?string $type = null, ?string $fromDate = null, ?string $toDate = null): array {
        return $this->getRepo()->findFiltered($this->getUserId(), $accountId, $categoryId, $type, $fromDate, $toDate);
    }
    public function isAuthorized(): bool {
        SessionsManager::start('wf-session');

        return SessionsManager::get('user-id') !== null;
    }

    private function getRepo(): TransactionRepository {
        return new TransactionRepository(new Database(App::getConfig()->getDBConnection('finance')));
    }

    private function getUserId(): int {
        return (int) SessionsManager::get('user-id');
    }
}
