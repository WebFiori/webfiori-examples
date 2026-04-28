<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Protects admin routes by requiring an active session with an author ID.
 *
 * If the user is not authenticated, they are redirected to the login page.
 * This middleware is assigned to admin routes via the 'auth' group.
 */
class AuthMiddleware extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('auth');
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
        SessionsManager::start('wf-session');
        $authorId = SessionsManager::get('author-id');

        if ($authorId === null) {
            $response->setCode(302);
            $response->addHeader('Location', '/login');
            $response->send();
        }
    }
}
