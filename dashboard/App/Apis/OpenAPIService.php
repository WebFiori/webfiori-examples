<?php
namespace App\Apis;

use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;
use WebFiori\Json\Json;

/**
 * Returns the OpenAPI 3.1.0 specification for all registered services.
 */
#[RestController('openapi', 'OpenAPI specification endpoint')]
class OpenAPIService extends WebService {
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function getSpec(): Json {
        $openApi = $this->getManager()->toOpenAPI();
        $info = $openApi->getInfo();
        $info->setTitle('Dashboard API');
        $info->setVersion('1.0.0');
        $info->setDescription('Multi-Tenant Dashboard REST API with role-based access control.');

        return $openApi->toJSON();
    }
}
