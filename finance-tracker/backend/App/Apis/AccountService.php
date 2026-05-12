<?php
namespace App\Apis;

use App\Domain\Account;
use App\Infrastructure\Repository\AccountRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Annotations\DeleteMapping;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\PutMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

#[RestController('accounts', 'Accounts — manage financial accounts.')]
class AccountService extends WebService {
    #[PostMapping]
    #[ResponseBody]
    #[RequestParam(name: 'name', type: ParamType::STRING, description: 'Account name.')]
    #[RequestParam(name: 'type', type: ParamType::STRING, optional: true, default: 'checking', description: 'Type: checking, savings, credit, cash.')]
    #[RequestParam(name: 'balance', type: ParamType::DOUBLE, optional: true, default: 0, description: 'Initial balance.')]
    public function createAccount(?string $name = null, ?string $type = null, ?float $balance = null): array {
        $account = new Account(userId: $this->getUserId(), name: $name, type: $type ?? 'checking', balance: $balance ?? 0, createdAt: date('Y-m-d H:i:s'));
        $this->getRepo()->save($account);

        return [$account];
    }

    #[DeleteMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Account ID.')]
    public function deleteAccount(?int $id = null): array {
        $repo = $this->getRepo();
        $account = $repo->findById($id);

        if ($account === null || $account->userId !== $this->getUserId()) {
            throw new NotFoundException('Account not found.');
        }

        $repo->deleteById($id);

        return [$account];
    }

    #[GetMapping]
    #[ResponseBody]
    public function getAccounts(): array {
        return $this->getRepo()->findByUserId($this->getUserId());
    }
    public function isAuthorized(): bool {
        SessionsManager::start('wf-session');

        return SessionsManager::get('user-id') !== null;
    }

    #[PutMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Account ID.')]
    #[RequestParam(name: 'name', type: ParamType::STRING, optional: true)]
    #[RequestParam(name: 'type', type: ParamType::STRING, optional: true)]
    #[RequestParam(name: 'balance', type: ParamType::DOUBLE, optional: true)]
    public function updateAccount(?int $id = null, ?string $name = null, ?string $type = null, ?float $balance = null): array {
        $repo = $this->getRepo();
        $account = $repo->findById($id);

        if ($account === null || $account->userId !== $this->getUserId()) {
            throw new NotFoundException('Account not found.');
        }

        if ($name !== null) {
            $account->name = $name;
        }

        if ($type !== null) {
            $account->type = $type;
        }

        if ($balance !== null) {
            $account->balance = $balance;
        }

        $repo->save($account);

        return [$account];
    }

    private function getRepo(): AccountRepository {
        return new AccountRepository(new Database(App::getConfig()->getDBConnection('finance')));
    }

    private function getUserId(): int {
        return (int) SessionsManager::get('user-id');
    }
}
