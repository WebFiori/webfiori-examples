<?php
namespace App\Middleware;

use App\Infrastructure\Repository\UserRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\Access;
use WebFiori\Framework\App;
use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;
use WebFiori\Http\SecurityContext;

class SecurityContextLoader extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('security-context');
        $this->setPriority(35000);
        $this->addToGroup('tenant-api');
        $this->addToGroup('admin-api');
    }

    public function getDependencies(): array {
        return ['start-session'];
    }

    public function before(Request $request, Response $response) {
        $userId = SessionsManager::get('user-id');

        if ($userId === null) {
            SecurityContext::clear();

            return;
        }

        $db = new Database(App::getConfig()->getDBConnection('billing'));
        $user = (new UserRepository($db))->findById($userId);

        if ($user !== null && $user->isActive()) {
            SecurityContext::setCurrentUser($user);
            Access::assignRoleToUser($user->getId(), $user->role);
        } else {
            SecurityContext::clear();
        }
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }
}
