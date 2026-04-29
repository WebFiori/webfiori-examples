<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Checks that the logged-in user has the required privilege for the route.
 *
 * The required privilege is set as a route option via RouteOption::VALUES
 * with key 'privilege'. If not set, any authenticated user can access.
 */
class RoleCheckMiddleware extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('role-check');
        $this->setPriority(100);
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
        $route = \WebFiori\Framework\Router\Router::getRouteUri();

        if ($route === null) {
            return;
        }

        // Check if route has a required privilege
        $requiredPrivilege = $route->getParameterValue('privilege');

        if ($requiredPrivilege === null) {
            return;
        }

        $userPrivileges = SessionsManager::get('user-privileges') ?? [];

        if (!in_array($requiredPrivilege, $userPrivileges)) {
            $response->setCode(403);
            $response->addHeader('content-type', 'application/json');
            $response->write('{"message":"Forbidden. You do not have the required privilege.","type":"error","http-code":403}');
            $response->send();
        }
    }
}
