<?php
namespace App\Apis;

use App\Infrastructure\Repository\AuthorRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\UnauthorizedException;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * Authentication API for login.
 */
#[RestController('auth', 'Authentication API')]
class AuthService extends WebService {
    /**
     * Authenticates an author by email and password, starts a session.
     *
     * @throws UnauthorizedException If credentials are invalid.
     */
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'email', type: ParamType::EMAIL, description: 'Author email')]
    #[RequestParam(name: 'password', type: ParamType::STRING, description: 'Author password')]
    public function login(?string $email = null, ?string $password = null): array {
        $db = new Database(App::getConfig()->getDBConnection('blog'));
        $repo = new AuthorRepository($db);
        $author = $repo->findByEmail($email);

        if ($author === null || !password_verify($password, $author->passwordHash)) {
            throw new UnauthorizedException('Invalid email or password.');
        }

        SessionsManager::set('author-id', $author->id);
        SessionsManager::set('author-name', $author->name);

        return [$author];
    }
}
