<?php
namespace App\Apis;

use WebFiori\Http\WebServicesManager;

class OrderServicesManager extends WebServicesManager {
    public function __construct() {
        parent::__construct();
        $this->addService(new AuthService());
        $this->addService(new ProductService());
        $this->addService(new OrderService());
        $this->addService(new HealthService());
    }
}
