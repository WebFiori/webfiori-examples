<?php
namespace App\Apis;

use App\Domain\Budget;
use App\Infrastructure\Repository\BudgetRepository;
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

#[RestController('budgets', 'Budgets — manage spending budgets per category.')]
class BudgetService extends WebService {
    #[PostMapping]
    #[ResponseBody]
    #[RequestParam(name: 'categoryId', type: ParamType::INT, description: 'Category ID.')]
    #[RequestParam(name: 'amountLimit', type: ParamType::DOUBLE, description: 'Budget limit amount.')]
    #[RequestParam(name: 'period', type: ParamType::STRING, optional: true, default: 'monthly', description: 'Period: monthly or weekly.')]
    #[RequestParam(name: 'startDate', type: ParamType::STRING, description: 'Start date (Y-m-d).')]
    public function createBudget(?int $categoryId = null, ?float $amountLimit = null, ?string $period = null, ?string $startDate = null): array {
        $budget = new Budget(userId: $this->getUserId(), categoryId: $categoryId, amountLimit: $amountLimit, period: $period ?? 'monthly', startDate: $startDate);
        $this->getRepo()->save($budget);

        return [$budget];
    }

    #[DeleteMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Budget ID.')]
    public function deleteBudget(?int $id = null): array {
        $repo = $this->getRepo();
        $budget = $repo->findById($id);

        if ($budget === null || $budget->userId !== $this->getUserId()) {
            throw new NotFoundException('Budget not found.');
        }

        $repo->deleteById($id);

        return [$budget];
    }

    #[GetMapping]
    #[ResponseBody]
    public function getBudgets(): array {
        return $this->getRepo()->findByUserIdWithSpent($this->getUserId());
    }
    public function isAuthorized(): bool {
        SessionsManager::start('wf-session');

        return SessionsManager::get('user-id') !== null;
    }

    #[PutMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Budget ID.')]
    #[RequestParam(name: 'amountLimit', type: ParamType::DOUBLE, optional: true, description: 'New limit.')]
    #[RequestParam(name: 'period', type: ParamType::STRING, optional: true, description: 'New period.')]
    public function updateBudget(?int $id = null, ?float $amountLimit = null, ?string $period = null): array {
        $repo = $this->getRepo();
        $budget = $repo->findById($id);

        if ($budget === null || $budget->userId !== $this->getUserId()) {
            throw new NotFoundException('Budget not found.');
        }

        if ($amountLimit !== null) {
            $budget->amountLimit = $amountLimit;
        }

        if ($period !== null) {
            $budget->period = $period;
        }

        $repo->save($budget);

        return [$budget];
    }

    private function getRepo(): BudgetRepository {
        return new BudgetRepository(new Database(App::getConfig()->getDBConnection('finance')));
    }

    private function getUserId(): int {
        return (int) SessionsManager::get('user-id');
    }
}
