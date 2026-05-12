<?php
namespace App\Apis;

use App\Domain\User;
use App\Infrastructure\Repository\UserRepository;
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

#[RestController('auth', 'Authentication — register, login, and get profile.')]
class AuthService extends WebService {
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'email', type: ParamType::EMAIL, description: 'User email.')]
    #[RequestParam(name: 'password', type: ParamType::STRING, description: 'User password.')]
    #[RequestParam(name: 'name', type: ParamType::STRING, optional: true, description: 'Name (required for registration).')]
    #[RequestParam(name: 'register', type: ParamType::STRING, optional: true, description: 'Set to "true" to register instead of login.')]
    public function loginOrRegister(?string $email = null, ?string $password = null, ?string $name = null, ?string $register = null): array {
        $db = new Database(App::getConfig()->getDBConnection('finance'));
        $repo = new UserRepository($db);

        if ($register === 'true') {
            $user = new User(
                name: $name ?? '',
                email: $email,
                passwordHash: password_hash($password, PASSWORD_DEFAULT),
                createdAt: date('Y-m-d H:i:s')
            );
            $repo->save($user);
            $user = $repo->findByEmail($email);
        } else {
            $user = $repo->findByEmail($email);

            if ($user === null || !password_verify($password, $user->passwordHash)) {
                throw new UnauthorizedException('Invalid email or password.');
            }
        }

        SessionsManager::start('wf-session');
        SessionsManager::set('user-id', $user->id);
        SessionsManager::set('user-name', $user->name);
        $user->passwordHash = '';

        return [$user];
    }

    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function profile(): array {
        SessionsManager::start('wf-session');
        $userId = SessionsManager::get('user-id');

        if ($userId === null) {
            throw new UnauthorizedException('Not authenticated.');
        }

        $db = new Database(App::getConfig()->getDBConnection('finance'));
        $user = (new UserRepository($db))->findById((int) $userId);
        $user->passwordHash = '';

        return [$user];
    }
}
