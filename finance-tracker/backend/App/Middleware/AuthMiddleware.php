<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Verifies user is authenticated via session.
 */
class AuthMiddleware extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('auth');
        $this->setPriority(200);
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
        SessionsManager::start('wf-session');

        if (SessionsManager::get('user-id') === null) {
            $response->setCode(401);
            $response->addHeader('content-type', 'application/json');
            $response->write('{"message":"Not authenticated.","type":"error","http-code":401}');
            $response->send();
        }
    }
}
