<?php
namespace App\Apis;

use WebFiori\Framework\Health\HealthCheck;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

#[RestController('health', 'Health check API')]
class HealthService extends WebService {
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function check(): array {
        $result = HealthCheck::runAll();
        $this->getManager()->getResponse()->setCode($result['status'] === 'ok' ? 200 : 503);

        return $result;
    }
}
