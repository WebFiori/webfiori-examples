<?php
namespace App\Commands;

use App\Domain\Report;
use App\Infrastructure\Repository\ProjectRepository;
use App\Infrastructure\Repository\ReportRepository;
use WebFiori\Cli\Command;
use WebFiori\Database\Database;
use WebFiori\Framework\App;

class GenerateReportCommand extends Command {
    public function __construct() {
        parent::__construct('reports:generate', [], 'Generate a project summary report.');
    }

    public function exec(): int {
        $db = new Database(App::getConfig()->getDBConnection('dashboard'));
        $projects = (new ProjectRepository($db))->findAllWithOwner();
        $active = array_filter($projects, fn ($p) => $p->status === 'active');

        $content = "Report — ".date('Y-m-d H:i:s')."\nActive projects: ".count($active)."\n";

        foreach ($active as $p) {
            $content .= "- {$p->name}\n";
        }

        $storePath = APP_PATH.'Storage'.DS.'reports';

        if (!is_dir($storePath)) {
            mkdir($storePath, 0755, true);
        }

        $fileName = 'cli-report-'.date('Y-m-d-His').'.txt';
        file_put_contents($storePath.DS.$fileName, $content);

        $report = new Report(
            title: 'CLI Report — '.date('Y-m-d'),
            generatedBy: 1,
            filePath: $storePath.DS.$fileName,
            createdAt: date('Y-m-d H:i:s')
        );
        (new ReportRepository($db))->save($report);

        $this->success('Report generated: '.$fileName);

        return 0;
    }
}
