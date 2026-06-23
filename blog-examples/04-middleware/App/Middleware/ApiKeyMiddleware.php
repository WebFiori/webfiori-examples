<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Validates that requests have a valid API key header.
 * Depends on audit-log so requests are logged even if rejected.
 */
class ApiKeyMiddleware extends AbstractMiddleware {

    private string $validKey;

    public function __construct(string $apiKey = 'secret-key-123') {
        parent::__construct('api-key');
        $this->setPriority(1000);
        $this->validKey = $apiKey;
    }

    public function getDependencies(): array {
        return ['audit-log']; // audit-log runs first
    }

    public function before(Request $request, Response $response) {
        $key = $request->getHeader('x-api-key');

        if ($key !== $this->validKey) {
            $response->setCode(401);
            $response->write(json_encode(['error' => 'Invalid API key']));
            $response->send();
        }
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }
}
