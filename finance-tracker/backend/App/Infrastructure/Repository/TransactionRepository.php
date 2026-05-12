<?php
namespace App\Infrastructure\Repository;

use App\Domain\Transaction;
use WebFiori\Database\Repository\AbstractRepository;

class TransactionRepository extends AbstractRepository {
    /**
     * Finds transactions for a user with filters.
     *
     * @return Transaction[]
     */
    public function findFiltered(int $userId, ?int $accountId = null, ?int $categoryId = null, ?string $type = null, ?string $fromDate = null, ?string $toDate = null): array {
        $sql = 'SELECT t.*, a.name AS account_name, c.name AS category_name '
             .'FROM transactions t '
             .'LEFT JOIN accounts a ON t.account_id = a.id '
             .'LEFT JOIN categories c ON t.category_id = c.id '
             .'WHERE a.user_id = ?';
        $params = [$userId];

        if ($accountId !== null) {
            $sql .= ' AND t.account_id = ?';
            $params[] = $accountId;
        }

        if ($categoryId !== null) {
            $sql .= ' AND t.category_id = ?';
            $params[] = $categoryId;
        }

        if ($type !== null) {
            $sql .= ' AND t.type = ?';
            $params[] = $type;
        }

        if ($fromDate !== null) {
            $sql .= ' AND t.date >= ?';
            $params[] = $fromDate;
        }

        if ($toDate !== null) {
            $sql .= ' AND t.date <= ?';
            $params[] = $toDate;
        }

        $sql .= ' ORDER BY t.date DESC';

        return array_map(fn ($r) => $this->toEntity($r), $this->getDatabase()->raw($sql, $params)->execute()->fetchAll());
    }

    /**
     * Returns monthly income vs expenses for a user.
     */
    public function monthlyTrend(int $userId): array {
        $dbType = $this->getDatabase()->getConnectionInfo()->getDatabaseType();

        if ($dbType === 'mssql') {
            $monthExpr = "FORMAT(t.date, 'yyyy-MM')";
        } else {
            $monthExpr = "DATE_FORMAT(t.date, '%Y-%m')";
        }

        $sql = "SELECT $monthExpr AS month, t.type, SUM(t.amount) AS total "
             .'FROM transactions t '
             .'JOIN accounts a ON t.account_id = a.id '
             ."WHERE a.user_id = ? GROUP BY $monthExpr, t.type ORDER BY month";

        return $this->getDatabase()->raw($sql, [$userId])->execute()->fetchAll();
    }

    /**
     * Returns spending grouped by category for a user in a date range.
     */
    public function spendingByCategory(int $userId, ?string $fromDate = null, ?string $toDate = null): array {
        $sql = 'SELECT c.name, c.color, SUM(t.amount) AS total '
             .'FROM transactions t '
             .'JOIN accounts a ON t.account_id = a.id '
             .'LEFT JOIN categories c ON t.category_id = c.id '
             .'WHERE a.user_id = ? AND t.type = ?';
        $params = [$userId, 'expense'];

        if ($fromDate !== null) {
            $sql .= ' AND t.date >= ?';
            $params[] = $fromDate;
        }

        if ($toDate !== null) {
            $sql .= ' AND t.date <= ?';
            $params[] = $toDate;
        }

        $sql .= ' GROUP BY c.name, c.color';

        return $this->getDatabase()->raw($sql, $params)->execute()->fetchAll();
    }

    /**
     * Returns total income and expenses for a user in a date range.
     */
    public function summary(int $userId, ?string $fromDate = null, ?string $toDate = null): array {
        $sql = 'SELECT t.type, SUM(t.amount) AS total '
             .'FROM transactions t '
             .'JOIN accounts a ON t.account_id = a.id '
             .'WHERE a.user_id = ?';
        $params = [$userId];

        if ($fromDate !== null) {
            $sql .= ' AND t.date >= ?';
            $params[] = $fromDate;
        }

        if ($toDate !== null) {
            $sql .= ' AND t.date <= ?';
            $params[] = $toDate;
        }

        $sql .= ' GROUP BY t.type';

        $rows = $this->getDatabase()->raw($sql, $params)->execute()->fetchAll();
        $result = ['income' => 0, 'expense' => 0];

        foreach ($rows as $row) {
            $result[$row['type']] = (float) $row['total'];
        }

        $result['net'] = $result['income'] - $result['expense'];

        return $result;
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'transactions';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'account-id' => $entity->accountId,
            'category-id' => $entity->categoryId,
            'type' => $entity->type,
            'amount' => $entity->amount,
            'description' => $entity->description,
            'date' => $entity->date,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s'),
        ];
    }

    protected function toEntity(array $row): Transaction {
        return new Transaction(
            id: (int) $row['id'],
            accountId: (int) ($row['account_id'] ?? 0),
            categoryId: isset($row['category_id']) ? (int) $row['category_id'] : null,
            type: $row['type'] ?? 'expense',
            amount: (float) ($row['amount'] ?? 0),
            description: $row['description'] ?? '',
            date: $row['date'] ?? null,
            createdAt: $row['created_at'] ?? null,
            accountName: $row['account_name'] ?? null,
            categoryName: $row['category_name'] ?? null
        );
    }
}
