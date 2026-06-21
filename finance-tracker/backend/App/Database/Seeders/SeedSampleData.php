<?php
namespace App\Database\Seeders;

use App\Database\Migrations\CreateFinanceTables;
use App\Domain\Account;
use App\Domain\Budget;
use App\Domain\Category;
use App\Domain\Transaction;
use App\Domain\User;
use App\Infrastructure\Repository\AccountRepository;
use App\Infrastructure\Repository\BudgetRepository;
use App\Infrastructure\Repository\CategoryRepository;
use App\Infrastructure\Repository\TransactionRepository;
use App\Infrastructure\Repository\UserRepository;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractSeeder;

class SeedSampleData extends AbstractSeeder {
    public function getDependencies(): array {
        return [CreateFinanceTables::class];
    }
    public function getEnvironments(): array {
        return ['dev', 'test'];
    }

    public function run(Database $db): void {
        $now = date('Y-m-d H:i:s');

        // User
        $userRepo = new UserRepository($db);
        $userRepo->save(new User(name: 'Demo User', email: 'demo@example.com', passwordHash: password_hash('demo123', PASSWORD_DEFAULT), createdAt: $now));

        // Default categories (global, user_id = NULL)
        $catRepo = new CategoryRepository($db);
        $categories = [
            new Category(name: 'Salary', type: 'income', icon: 'wallet', color: '#27ae60'),
            new Category(name: 'Groceries', type: 'expense', icon: 'cart', color: '#e74c3c'),
            new Category(name: 'Rent', type: 'expense', icon: 'home', color: '#8e44ad'),
            new Category(name: 'Transport', type: 'expense', icon: 'car', color: '#f39c12'),
            new Category(name: 'Entertainment', type: 'expense', icon: 'film', color: '#3498db'),
            new Category(name: 'Freelance', type: 'income', icon: 'briefcase', color: '#2ecc71'),
        ];

        foreach ($categories as $c) {
            $catRepo->save($c);
        }

        // Accounts
        $accRepo = new AccountRepository($db);
        $accRepo->save(new Account(userId: 1, name: 'Main Checking', type: 'checking', balance: 5000, createdAt: $now));
        $accRepo->save(new Account(userId: 1, name: 'Savings', type: 'savings', balance: 12000, createdAt: $now));

        // Transactions
        $txRepo = new TransactionRepository($db);
        $txRepo->save(new Transaction(accountId: 1, categoryId: 1, type: 'income', amount: 4500, description: 'Monthly salary', date: date('Y-m-01'), createdAt: $now));
        $txRepo->save(new Transaction(accountId: 1, categoryId: 2, type: 'expense', amount: 320, description: 'Weekly groceries', date: date('Y-m-05'), createdAt: $now));
        $txRepo->save(new Transaction(accountId: 1, categoryId: 3, type: 'expense', amount: 1200, description: 'Monthly rent', date: date('Y-m-01'), createdAt: $now));
        $txRepo->save(new Transaction(accountId: 1, categoryId: 4, type: 'expense', amount: 85, description: 'Gas', date: date('Y-m-10'), createdAt: $now));
        $txRepo->save(new Transaction(accountId: 1, categoryId: 5, type: 'expense', amount: 45, description: 'Movie tickets', date: date('Y-m-12'), createdAt: $now));

        // Budget
        $budgetRepo = new BudgetRepository($db);
        $budgetRepo->save(new Budget(userId: 1, categoryId: 2, amountLimit: 500, period: 'monthly', startDate: date('Y-m-01')));
        $budgetRepo->save(new Budget(userId: 1, categoryId: 5, amountLimit: 100, period: 'monthly', startDate: date('Y-m-01')));
    }
}
