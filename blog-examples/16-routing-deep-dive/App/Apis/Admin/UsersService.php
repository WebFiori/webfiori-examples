<?php
namespace App\Apis\Admin;

use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\WebService;

/**
 * Route: GET /apis/admin/users
 *
 * No #[RestController] attribute — name is auto-derived from class name.
 * 'UsersService' -> strips 'Service' -> 'Users' -> kebab -> 'users'
 * Subdirectory 'Admin' -> kebab -> 'admin/' is prepended as path prefix.
 * Result with recursive discovery: /apis/admin/users
 */
class UsersService extends WebService {

    public function __construct() {
        parent::__construct('users');
    }

    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function listUsers(): array {
        return [
            ['id' => 1, 'name' => 'Alice', 'role' => 'admin'],
            ['id' => 2, 'name' => 'Bob',   'role' => 'user'],
        ];
    }
}
