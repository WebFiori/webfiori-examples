<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Adds CORS headers for SPA frontend access with credentials support.
 */
class CorsMiddleware extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('cors');
        $this->setPriority(1);
    }

    public function after(Request $request, Response $response) {
        if ($request->getMethod() === 'OPTIONS') {
            return;
        }

        $this->addCorsHeaders($request, $response);

        // Fix session cookie SameSite for cross-origin requests.
        // start-session.after() already added the set-cookie header with SameSite=Lax.
        // Remove it and re-add with SameSite=None.
        $response->removeHeader('set-cookie');

        $sessions = SessionsManager::getSessions();

        foreach ($sessions as $session) {
            $session->setSameSite('None');
            $session->getCookie()->setIsSecure(false);
            $response->addHeader('set-cookie', $session->getCookieHeader());
        }
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
        if ($request->getMethod() === 'OPTIONS') {
            $this->addCorsHeaders($request, $response);
            $response->setCode(204);
            $response->send();
        }
    }

    private function addCorsHeaders(Request $request, Response $response) {
        $origin = $request->getHeader('origin');

        if (is_array($origin)) {
            $origin = $origin[0];
        }

        if ($origin !== null) {
            $response->addHeader('Access-Control-Allow-Origin', $origin);
        }

        $response->addHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->addHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->addHeader('Access-Control-Allow-Credentials', 'true');
        $response->addHeader('Access-Control-Max-Age', '86400');
    }
}
