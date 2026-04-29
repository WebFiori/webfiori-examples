<?php
namespace App\Apis;

use WebFiori\Http\WebServicesManager;

class LinkServicesManager extends WebServicesManager {
    public function __construct() {
        parent::__construct();
        $this->addService(new ShortLinkService());
        $this->addService(new HealthCheckService());
    }
}
