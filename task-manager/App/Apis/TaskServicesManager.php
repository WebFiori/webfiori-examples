<?php
namespace App\Apis;

use WebFiori\Http\WebServicesManager;

/**
 * Services manager that registers all task-related API endpoints.
 *
 * This class is the target of the API route defined in APIsRoutes.
 * The framework instantiates it when a request matches `/apis/{service}`
 * and dispatches to the appropriate service based on the `service` path
 * parameter and the HTTP method.
 */
class TaskServicesManager extends WebServicesManager {
    public function __construct() {
        parent::__construct();
        $this->addService(new TaskService());
    }
}
