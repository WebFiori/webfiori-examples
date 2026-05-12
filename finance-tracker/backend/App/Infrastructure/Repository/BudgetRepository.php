<?php
namespace App\Infrastructure\Repository;

use App\Domain\Budget;
use WebFiori\Database\Repository\AbstractRepository;

class BudgetRepository extends AbstractRepository {
    /**
     * Returns budgets for a user with spent amounts calculated.
     *
     * @return Budget[]
     */
    public function findByUserIdWithSpent(int $userId): array {
        $dbType = $this->getDatabase()->getConnectionInfo()->getDatabaseType();

        if ($dbType === 'mssql') {
            $monthExpr = "FORMAT(t.date, 'yyyy-MM')";
            $curMonth = "FORMAT(GETDATE(), 'yyyy-MM')";
        } else {
            $monthExpr = "DATE_FORMAT(t.date, '%Y-%m')";
            $curMonth = "DATE_FORMAT(NOW(), '%Y-%m')";
        }

        $sql = "SELECT b.*, c.name AS category_name, "
             ."COALESCE((SELECT SUM(t.amount) FROM transactions t "
             ."JOIN accounts a ON t.account_id = a.id "
             ."WHERE t.category_id = b.category_id AND a.user_id = b.user_id "
             ."AND t.type = 'expense' AND $monthExpr = $curMonth), 0) AS spent "
             ."FROM budgets b "
             ."LEFT JOIN categories c ON b.category_id = c.id "
             ."WHERE b.user_id = ?";

        return array_map(fn ($r) => $this->toEntity($r), $this->getDatabase()->raw($sql, [$userId])->execute()->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'budgets';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'user-id' => $entity->userId,
            'category-id' => $entity->categoryId,
            'amount-limit' => $entity->amountLimit,
            'period' => $entity->period,
            'start-date' => $entity->startDate,
        ];
    }

    protected function toEntity(array $row): Budget {
        return new Budget(
            id: (int) $row['id'],
            userId: (int) ($row['user_id'] ?? 0),
            categoryId: (int) ($row['category_id'] ?? 0),
            amountLimit: (float) ($row['amount_limit'] ?? 0),
            period: $row['period'] ?? 'monthly',
            startDate: $row['start_date'] ?? null,
            categoryName: $row['category_name'] ?? null,
            spent: (float) ($row['spent'] ?? 0)
        );
    }
}
