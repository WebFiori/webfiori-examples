<?php
namespace App\Apis;

use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\ApiResponse;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\OpenAPI\OpenAPIGenerator;
use WebFiori\Http\WebService;
use WebFiori\Json\Json;

/**
 * Serves the OpenAPI 3.1 specification using namespace scanning.
 */
#[RestController('openapi', 'OpenAPI specification endpoint')]
class OpenAPIService extends WebService {

    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[ApiResponse(status: '200', description: 'OpenAPI 3.1 JSON specification')]
    public function getSpec(): Json {
        $generator = new OpenAPIGenerator();

        $spec = $generator->generateFromNamespace(
            'App\\Apis',
            'WebFiori Blog Example API',
            '1.0.0',
            '/apis'
        );

        return $spec->toJSON();
    }
}
