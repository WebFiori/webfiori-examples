<?php
namespace App\Tasks;

use App\Domain\Report;
use App\Infrastructure\Repository\ProjectRepository;
use App\Infrastructure\Repository\ReportRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Scheduler\AbstractTask;

class GenerateWeeklyReportTask extends AbstractTask {
    public function __construct() {
        parent::__construct('generate-weekly-report', '0 7 * * 1', 'Generates weekly project summary report.');
    }

    public function afterExec(): void {
    }

    public function execute(): void {
        $db = new Database(App::getConfig()->getDBConnection('dashboard'));
        $projects = (new ProjectRepository($db))->findAllWithOwner();

        $active = array_filter($projects, fn ($p) => $p->status === 'active');
        $completed = array_filter($projects, fn ($p) => $p->status === 'completed');

        $content = "Weekly Report — ".date('Y-m-d')."\n";
        $content .= "Active: ".count($active)." | Completed: ".count($completed)."\n\n";

        foreach ($active as $p) {
            $content .= "- {$p->name} (Owner: ".($p->ownerName ?? 'N/A').")\n";
        }

        $storePath = APP_PATH.'Storage'.DS.'reports';

        if (!is_dir($storePath)) {
            mkdir($storePath, 0755, true);
        }

        $fileName = 'weekly-'.date('Y-m-d').'.txt';
        file_put_contents($storePath.DS.$fileName, $content);

        $report = new Report(
            title: 'Weekly Report — '.date('Y-m-d'),
            generatedBy: 1,
            filePath: $storePath.DS.$fileName,
            createdAt: date('Y-m-d H:i:s')
        );
        (new ReportRepository($db))->save($report);
    }

    public function onFail(): void {
    }

    public function onSuccess(): void {
    }
}
