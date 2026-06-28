<?php
namespace App\Apis;

use App\Jobs\GenerateReportJob;
use App\Jobs\SendWelcomeEmailJob;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;
use WebFiori\Queue\QueueFacade;

/**
 * API to dispatch jobs and check queue status.
 */
#[RestController('jobs', 'Job queue API')]
class JobService extends WebService {

    #[PostMapping('/dispatch-email')]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'email', type: ParamType::EMAIL)]
    #[RequestParam(name: 'name', type: ParamType::STRING)]
    public function dispatchEmail(string $email, string $name): array {
        $id = QueueFacade::dispatch(new SendWelcomeEmailJob($email, $name));

        return ['job_id' => $id, 'status' => 'queued'];
    }

    #[PostMapping('/dispatch-report')]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'report-id', type: ParamType::INT)]
    public function dispatchReport(int $reportId): array {
        $id = QueueFacade::dispatch(new GenerateReportJob($reportId), priority: 5);

        return ['job_id' => $id, 'status' => 'queued', 'priority' => 5];
    }

    #[GetMapping('/status')]
    #[ResponseBody]
    #[AllowAnonymous]
    public function status(): array {
        return [
            'pending' => QueueFacade::getPendingCount(),
            'failed' => count(QueueFacade::getFailed()),
        ];
    }
}
