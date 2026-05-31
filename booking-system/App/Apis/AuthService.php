<?php
namespace App\Apis;

use App\Infrastructure\Repository\UserRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\Access;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\UnauthorizedException;
use WebFiori\Http\ParamType;
use WebFiori\Http\SecurityContext;
use WebFiori\Http\WebService;

#[RestController('auth', 'Authentication API')]
class AuthService extends WebService {
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'email', type: ParamType::EMAIL)]
    #[RequestParam(name: 'password', type: ParamType::STRING)]
    public function login(?string $email = null, ?string $password = null): array {
        $db = new Database(App::getConfig()->getDBConnection('booking'));
        $user = (new UserRepository($db))->findByEmail($email);

        if ($user === null || !password_verify($password, $user->passwordHash)) {
            throw new UnauthorizedException('Invalid credentials.');
        }

        SessionsManager::set('user-id', $user->id);
        SecurityContext::setCurrentUser($user);
        Access::assignRoleToUser($user->getId(), $user->role);

        return ['id' => $user->id, 'name' => $user->name, 'role' => $user->role];
    }
}
