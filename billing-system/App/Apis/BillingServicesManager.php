<?php
namespace App\Apis;

use WebFiori\Http\WebServicesManager;

class BillingServicesManager extends WebServicesManager {
    public function __construct() {
        parent::__construct();
        $this->addService(new AuthService());
        $this->addService(new TenantService());
        $this->addService(new InvoiceService());
        $this->addService(new HealthService());
    }
}
