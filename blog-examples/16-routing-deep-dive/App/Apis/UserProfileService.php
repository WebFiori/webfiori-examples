<?php
namespace App\Apis;

use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

/**
 * Route: GET /apis/user-profile
 *
 * 'UserProfileService' → strips 'Service' → 'UserProfile' → kebab → 'user-profile'
 */
#[RestController('user-profile', 'User profile API')]
class UserProfileService extends WebService {

    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function getProfile(): array {
        return ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'];
    }
}
