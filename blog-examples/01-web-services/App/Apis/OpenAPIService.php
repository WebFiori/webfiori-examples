<?php
namespace App\Apis;

use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\OpenAPI\OpenAPIGenerator;
use WebFiori\Http\WebService;
use WebFiori\Json\Json;

/**
 * Serves the OpenAPI 3.1 specification for all registered services.
 *
 * Access at GET /apis/openapi to get the JSON spec.
 * Feed the output to Swagger UI, Postman, or any OpenAPI-compatible tool.
 */
#[RestController('openapi', 'OpenAPI specification endpoint')]
class OpenAPIService extends WebService {

    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function getSpec(): Json {
        $generator = new OpenAPIGenerator();

        $spec = $generator->generate(
            [new ProductService(), new UserService()],
            'Product & User API — WebFiori v3 Blog Example',
            '1.0.0',
            '/apis'
        );

        $spec->getInfo()->setTitle('WebFiori Blog Example API');

        return $spec->toJSON();
    }
}
