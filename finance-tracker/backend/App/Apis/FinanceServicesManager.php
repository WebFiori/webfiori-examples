<?php
namespace App\Apis;

use WebFiori\Http\WebServicesManager;

class FinanceServicesManager extends WebServicesManager {
    public function __construct() {
        parent::__construct();
        $this->addService(new AuthService());
        $this->addService(new AccountService());
        $this->addService(new CategoryService());
        $this->addService(new TransactionService());
        $this->addService(new BudgetService());
        $this->addService(new AnalyticsService());
    }
}
