<?php
namespace App\Apis;

use App\Infrastructure\Repository\UserRepository;
use App\Ini\Privileges;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\UnauthorizedException;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * Handles user authentication: login and profile retrieval.
 */
#[RestController('auth', 'Authentication — login and retrieve current user profile.')]
class AuthService extends WebService {
    /**
     * Authenticates a user by email and password. Starts a session and
     * stores user ID, name, role, and privileges.
     */
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'email', type: ParamType::EMAIL, description: 'User email address.')]
    #[RequestParam(name: 'password', type: ParamType::STRING, description: 'User password.')]
    public function login(?string $email = null, ?string $password = null): array {
        $db = new Database(App::getConfig()->getDBConnection('dashboard'));
        $user = (new UserRepository($db))->findByEmail($email);

        if ($user === null || !$user->isActive || !password_verify($password, $user->passwordHash)) {
            throw new UnauthorizedException('Invalid email or password.');
        }

        SessionsManager::start('wf-session');
        SessionsManager::set('user-id', $user->id);
        SessionsManager::set('user-name', $user->name);
        SessionsManager::set('user-role', $user->role);
        SessionsManager::set('user-privileges', Privileges::privilegesForRole($user->role));

        return ['id' => $user->id, 'name' => $user->name, 'role' => $user->role];
    }

    /**
     * Returns the currently authenticated user's profile and privileges.
     */
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function profile(): array {
        SessionsManager::start('wf-session');
        $userId = SessionsManager::get('user-id');

        if ($userId === null) {
            throw new UnauthorizedException('Not authenticated.');
        }

        return [
            'id' => $userId,
            'name' => SessionsManager::get('user-name'),
            'role' => SessionsManager::get('user-role'),
            'privileges' => SessionsManager::get('user-privileges') ?? [],
        ];
    }
}
