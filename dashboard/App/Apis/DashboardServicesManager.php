<?php
namespace App\Apis;

use WebFiori\Http\WebServicesManager;

class DashboardServicesManager extends WebServicesManager {
    public function __construct() {
        parent::__construct();
        $this->addService(new AuthService());
        $this->addService(new UserService());
        $this->addService(new ProjectService());
        $this->addService(new ReportService());
        $this->addService(new AuditLogService());
        $this->addService(new OpenAPIService());
    }
}
