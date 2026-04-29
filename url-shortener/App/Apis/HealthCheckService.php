<?php
namespace App\Apis;

use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

/**
 * Health check endpoint.
 */
#[RestController('health', 'Health check API')]
class HealthCheckService extends WebService {
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function check(): array {
        try {
            $db = new Database(App::getConfig()->getDBConnection('shortener'));
            $db->getConnection();

            return ['status' => 'ok', 'database' => 'connected'];
        } catch (\Throwable $e) {
            App::getResponse()->setCode(503);

            return ['status' => 'error', 'database' => 'disconnected'];
        }
    }
}
