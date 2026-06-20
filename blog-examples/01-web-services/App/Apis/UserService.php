<?php
namespace App\Apis;

use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Annotations\Validate;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * User registration API demonstrating cross-field validation with #[Validate].
 */
#[RestController('users', 'User registration API')]
class UserService extends WebService {

    /**
     * Register a new user with password confirmation validation.
     *
     * Demonstrates #[Validate] — the framework calls the validation method
     * after parameter filtering. If it returns a non-empty array, the
     * framework responds with 422 and the error messages.
     */
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'name', type: ParamType::STRING, description: 'Full name')]
    #[RequestParam(name: 'email', type: 'email', description: 'Email address')]
    #[RequestParam(name: 'password', type: ParamType::STRING, description: 'Password (min 8 chars)')]
    #[RequestParam(name: 'password_confirm', type: ParamType::STRING, description: 'Password confirmation')]
    #[Validate('validateRegistration')]
    public function register(): array {
        return [
            'message' => 'User registered',
            'user' => [
                'name' => $this->getParamVal('name'),
                'email' => $this->getParamVal('email'),
            ]
        ];
    }

    /**
     * Cross-field validation for registration.
     *
     * @param array $inputs Filtered input values.
     *
     * @return array Associative array of field => error message. Empty = valid.
     */
    private function validateRegistration(array $inputs): array {
        $errors = [];

        if ($inputs['password'] !== $inputs['password_confirm']) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }

        if (strlen($inputs['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        return $errors;
    }
}
