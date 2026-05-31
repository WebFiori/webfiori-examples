<?php
namespace App\Apis;

use WebFiori\Framework\Health\HealthCheck;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

/**
 * Health check endpoint. Returns 200 if all checks pass, 503 otherwise.
 */
#[RestController('health', 'Health check API')]
class HealthService extends WebService {
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function check(): array {
        $result = HealthCheck::runAll();
        $code = $result['status'] === 'ok' ? 200 : 503;
        $this->getManager()->getResponse()->setCode($code);

        return $result;
    }
}
