<?php
namespace App\Apis;

use App\Jobs\GenerateReportJob;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;
use WebFiori\Queue\QueueFacade;

#[RestController('dispatch-report', 'Dispatch report job')]
class DispatchReportService extends WebService {

    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'report-id', type: ParamType::INT)]
    public function dispatch(int $reportId): array {
        $id = QueueFacade::dispatch(new GenerateReportJob($reportId), priority: 5);

        return ['job_id' => $id, 'status' => 'queued', 'priority' => 5];
    }
}
