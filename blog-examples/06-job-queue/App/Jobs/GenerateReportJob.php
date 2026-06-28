<?php
namespace App\Jobs;

use WebFiori\Log\LoggerFacade;
use WebFiori\Queue\Job;

/**
 * Simulates generating a PDF report. Fails if reportId > 900 (for testing retries).
 */
class GenerateReportJob implements Job {
    public function __construct(
        private int $reportId,
        private string $format = 'pdf'
    ) {}

    public function getMaxAttempts(): int {
        return 2;
    }

    public function getRetryDelaySeconds(): int {
        return 0;
    }

    public function handle(): void {
        if ($this->reportId > 900) {
            throw new \RuntimeException("Report service unavailable for ID {$this->reportId}");
        }

        LoggerFacade::info('Report generated', [
            'report_id' => $this->reportId,
            'format' => $this->format,
        ]);
    }

    public function getReportId(): int {
        return $this->reportId;
    }
}
