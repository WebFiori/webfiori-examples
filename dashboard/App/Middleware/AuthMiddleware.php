<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Verifies user is logged in. Redirects to /login if not.
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
            $response->setCode(302);
            $response->addHeader('Location', '/login');
            $response->send();
        }
    }
}
