<?php
namespace App\Apis;

use App\Infrastructure\Repository\TenantRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PreAuthorize;
use WebFiori\Http\Annotations\RequiresAuth;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\SecurityContext;
use WebFiori\Http\WebService;

#[RestController('tenants', 'Tenant management API')]
#[RequiresAuth]
class TenantService extends WebService {
    public function isAuthorized(): bool {
        return SecurityContext::isAuthenticated();
    }

    #[GetMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAuthority('tenants.manage')")]
    public function listTenants(): array {
        $db = new Database(App::getConfig()->getDBConnection('billing'));

        return (new TenantRepository($db))->findAll();
    }
}
