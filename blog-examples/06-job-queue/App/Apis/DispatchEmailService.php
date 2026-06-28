<?php
namespace App\Apis;

use App\Jobs\SendWelcomeEmailJob;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;
use WebFiori\Queue\QueueFacade;

#[RestController('dispatch-email', 'Dispatch email job')]
class DispatchEmailService extends WebService {

    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'email', type: ParamType::EMAIL)]
    #[RequestParam(name: 'name', type: ParamType::STRING)]
    public function dispatch(string $email, string $name): array {
        $id = QueueFacade::dispatch(new SendWelcomeEmailJob($email, $name));

        return ['job_id' => $id, 'status' => 'queued'];
    }
}
