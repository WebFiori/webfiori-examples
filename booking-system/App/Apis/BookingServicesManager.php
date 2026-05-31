<?php
namespace App\Apis;

use WebFiori\Http\WebServicesManager;

class BookingServicesManager extends WebServicesManager {
    public function __construct() {
        parent::__construct();
        $this->addService(new AuthService());
        $this->addService(new ServiceCatalogService());
        $this->addService(new AppointmentService());
        $this->addService(new HealthService());
    }
}
