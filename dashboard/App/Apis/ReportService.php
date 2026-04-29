<?php
namespace App\Apis;

use App\Domain\Report;
use App\Infrastructure\Repository\ProjectRepository;
use App\Infrastructure\Repository\ReportRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\RequestMethod;
use WebFiori\Http\WebService;

/**
 * Reports API. GET requires VIEW_REPORTS; POST requires GENERATE_REPORTS.
 */
#[RestController('reports', 'Reports — list generated reports or trigger report generation.')]
class ReportService extends WebService {
    public function isAuthorized(): bool {
        SessionsManager::start('wf-session');
        $privileges = SessionsManager::get('user-privileges') ?? [];
        $method = $this->getManager()?->getRequest()?->getMethod() ?? '';

        if ($method === RequestMethod::GET) {
            return in_array('VIEW_REPORTS', $privileges);
        }

        return in_array('GENERATE_REPORTS', $privileges);
    }

    /**
     * Returns all generated reports with the name of the user who generated each.
     */
    #[GetMapping]
    #[ResponseBody]
    public function getReports(): array {
        $db = new Database(App::getConfig()->getDBConnection('dashboard'));

        return (new ReportRepository($db))->findAllWithUser();
    }

    /**
     * Generates a project summary report. Aggregates active/completed project
     * counts, stores the report as a text file, and records it in the database.
     */
    #[PostMapping]
    #[ResponseBody]
    public function generateReport(): array {
        $db = new Database(App::getConfig()->getDBConnection('dashboard'));
        $projects = (new ProjectRepository($db))->findAllWithOwner();

        $content = "Weekly Report — ".date('Y-m-d')."\n\n";
        $active = array_filter($projects, fn ($p) => $p->status === 'active');
        $completed = array_filter($projects, fn ($p) => $p->status === 'completed');
        $content .= "Active projects: ".count($active)."\n";
        $content .= "Completed projects: ".count($completed)."\n";

        $storePath = APP_PATH.'Storage'.DS.'reports';

        if (!is_dir($storePath)) {
            mkdir($storePath, 0755, true);
        }

        $fileName = 'report-'.date('Y-m-d-His').'.txt';
        file_put_contents($storePath.DS.$fileName, $content);

        $userId = (int) SessionsManager::get('user-id');
        $report = new Report(
            title: 'Weekly Report — '.date('Y-m-d'),
            generatedBy: $userId,
            filePath: $storePath.DS.$fileName,
            createdAt: date('Y-m-d H:i:s')
        );

        (new ReportRepository($db))->save($report);

        return [$report];
    }
}
