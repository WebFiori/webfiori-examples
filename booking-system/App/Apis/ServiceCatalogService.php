<?php
namespace App\Apis;

use App\Infrastructure\Repository\ServiceRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;

#[RestController('services', 'Service catalog API')]
class ServiceCatalogService extends WebService {
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function listServices(): array {
        $db = new Database(App::getConfig()->getDBConnection('booking'));

        return (new ServiceRepository($db))->findAll();
    }
}
