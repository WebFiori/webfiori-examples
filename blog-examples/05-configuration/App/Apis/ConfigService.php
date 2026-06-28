<?php
namespace App\Apis;

use WebFiori\Framework\App;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

/**
 * Exposes application configuration (non-sensitive) as an API.
 */
#[RestController('config', 'Configuration info API')]
class ConfigService extends WebService {
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function getConfig(): array {
        $config = App::getConfig();

        return [
            'app_name' => $config->getWebsiteName('EN'),
            'version' => $config->getVersion(),
            'environment' => defined('APP_ENV') ? APP_ENV : 'unknown',
            'primary_language' => $config->getPrimaryLanguage(),
            'max_upload_size' => defined('MAX_UPLOAD_SIZE') ? MAX_UPLOAD_SIZE : null,
            'api_rate_limit' => defined('API_RATE_LIMIT') ? API_RATE_LIMIT : null,
        ];
    }
}
