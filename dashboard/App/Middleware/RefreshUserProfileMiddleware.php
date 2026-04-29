<?php
namespace App\Middleware;

use App\Infrastructure\Repository\UserRepository;
use App\Ini\Privileges;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Reloads user privileges from DB on each request.
 */
class RefreshUserProfileMiddleware extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('refresh-profile');
        $this->setPriority(150);
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
        $userId = SessionsManager::get('user-id');

        if ($userId !== null) {
            $db = new Database(App::getConfig()->getDBConnection('dashboard'));
            $user = (new UserRepository($db))->findById((int) $userId);

            if ($user !== null && $user->isActive) {
                SessionsManager::set('user-role', $user->role);
                SessionsManager::set('user-name', $user->name);
                SessionsManager::set('user-privileges', Privileges::privilegesForRole($user->role));
            } else {
                // User deactivated or not found — force logout
                SessionsManager::destroy();
                $response->setCode(302);
                $response->addHeader('Location', '/login');
                $response->send();
            }
        }
    }
}
