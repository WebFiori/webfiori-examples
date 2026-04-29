<?php
namespace App\Apis;

use App\Domain\User;
use App\Infrastructure\Repository\UserRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Annotations\DeleteMapping;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\PutMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\RequestMethod;
use WebFiori\Http\WebService;

/**
 * User management API. GET requires authentication; POST/PUT/DELETE require MANAGE_USERS privilege (Admin only).
 */
#[RestController('users', 'User management — list, create, update, and deactivate users.')]
class UserService extends WebService {
    public function isAuthorized(): bool {
        $method = $this->getManager()?->getRequest()?->getMethod() ?? '';
        SessionsManager::start('wf-session');
        $privileges = SessionsManager::get('user-privileges') ?? [];

        if ($method === RequestMethod::GET) {
            return SessionsManager::get('user-id') !== null;
        }

        return in_array('MANAGE_USERS', $privileges);
    }

    /**
     * Lists all users or returns a single user by ID. Passwords are excluded from the response.
     */
    #[GetMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true, description: 'User ID. If omitted, all users are returned.')]
    public function getUsers(?int $id = null): array {
        $repo = $this->getRepo();

        if ($id !== null) {
            $user = $repo->findById($id);

            if ($user === null) {
                throw new NotFoundException('User not found.');
            }

            $user->passwordHash = '';

            return [$user];
        }

        $users = $repo->findAll();

        foreach ($users as $u) {
            $u->passwordHash = '';
        }

        return $users;
    }

    /**
     * Creates a new user. Sends a welcome email on success.
     */
    #[PostMapping]
    #[ResponseBody]
    #[RequestParam(name: 'name', type: ParamType::STRING, description: 'Full name of the user.')]
    #[RequestParam(name: 'email', type: ParamType::EMAIL, description: 'Email address (must be unique).')]
    #[RequestParam(name: 'password', type: ParamType::STRING, description: 'Initial password.')]
    #[RequestParam(name: 'role', type: ParamType::STRING, optional: true, default: 'viewer', description: 'Role: admin, manager, or viewer.')]
    public function createUser(?string $name = null, ?string $email = null, ?string $password = null, ?string $role = null): array {
        $user = new User(
            name: $name,
            email: $email,
            passwordHash: password_hash($password, PASSWORD_DEFAULT),
            role: $role ?? 'viewer',
            createdAt: date('Y-m-d H:i:s')
        );
        $this->getRepo()->save($user);
        $user->passwordHash = '';

        // Send welcome email
        try {
            $smtp = App::getConfig()->getSMTPConnection('no-reply');

            if ($smtp !== null) {
                $message = new \WebFiori\Framework\EmailMessage('no-reply');
            } else {
                $message = new \WebFiori\Mail\Email(new \WebFiori\Mail\SMTPAccount());
                $storePath = APP_PATH.'Storage'.DS.'Logs'.DS.'emails';

                if (!is_dir($storePath)) {
                    mkdir($storePath, 0755, true);
                }
                $message->setMode(\WebFiori\Mail\SendMode::TEST_STORE, ['store-path' => $storePath]);
            }
            $message->setSubject('Welcome to the Dashboard');
            $message->addTo($user->email, $user->name);
            $message->insert('h2')->text('Welcome, '.$user->name.'!');
            $message->insert('p')->text('Your account has been created with the role: '.$user->role);
            $message->send();
        } catch (\Throwable $e) {
        }

        return [$user];
    }

    /**
     * Updates an existing user's name, role, or active status.
     */
    #[PutMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'ID of the user to update.')]
    #[RequestParam(name: 'name', type: ParamType::STRING, optional: true, description: 'New name.')]
    #[RequestParam(name: 'role', type: ParamType::STRING, optional: true, description: 'New role: admin, manager, or viewer.')]
    #[RequestParam(name: 'isActive', type: ParamType::BOOL, optional: true, description: 'Set to false to deactivate.')]
    public function updateUser(?int $id = null, ?string $name = null, ?string $role = null, ?bool $isActive = null): array {
        $repo = $this->getRepo();
        $user = $repo->findById($id);

        if ($user === null) {
            throw new NotFoundException('User not found.');
        }

        if ($name !== null) {
            $user->name = $name;
        }

        if ($role !== null) {
            $user->role = $role;
        }

        if ($isActive !== null) {
            $user->isActive = $isActive;
        }

        $user->updatedAt = date('Y-m-d H:i:s');
        $repo->save($user);
        $user->passwordHash = '';

        return [$user];
    }

    /**
     * Deactivates a user (soft delete). The user can no longer log in.
     */
    #[DeleteMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'ID of the user to deactivate.')]
    public function deactivateUser(?int $id = null): array {
        $repo = $this->getRepo();
        $user = $repo->findById($id);

        if ($user === null) {
            throw new NotFoundException('User not found.');
        }

        $user->isActive = false;
        $user->updatedAt = date('Y-m-d H:i:s');
        $repo->save($user);
        $user->passwordHash = '';

        return [$user];
    }

    private function getRepo(): UserRepository {
        return new UserRepository(new Database(App::getConfig()->getDBConnection('dashboard')));
    }
}
