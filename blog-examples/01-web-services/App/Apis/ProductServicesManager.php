<?php
namespace App\Apis;

use WebFiori\Http\WebServicesManager;

/**
 * Services manager used for testing.
 *
 * In production, ServiceRouter handles discovery and routing automatically.
 * For tests, we use this manager with APITestCase to simulate requests.
 */
class ProductServicesManager extends WebServicesManager {
    public function __construct() {
        parent::__construct();
        $this->addService(new ProductService());
        $this->addService(new UserService());
    }
}
