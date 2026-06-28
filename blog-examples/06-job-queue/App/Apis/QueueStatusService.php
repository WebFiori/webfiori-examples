<?php
namespace App\Apis;

use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\WebService;
use WebFiori\Queue\QueueFacade;

#[RestController('queue-status', 'Queue status API')]
class QueueStatusService extends WebService {

    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function status(): array {
        return [
            'pending' => QueueFacade::getPendingCount(),
            'failed' => count(QueueFacade::getFailed()),
        ];
    }
}
