<?php
namespace Tests;

use App\Jobs\GenerateReportJob;
use App\Jobs\SendWelcomeEmailJob;
use PHPUnit\Framework\TestCase;
use WebFiori\Queue\Job;
use WebFiori\Queue\FileQueueStorage;
use WebFiori\Queue\Queue;
use WebFiori\Queue\QueueFacade;

class JobQueueTest extends TestCase {
    private string $tmpDir;

    protected function setUp(): void {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir() . '/wf-queue-test-' . uniqid();
        mkdir($this->tmpDir, 0755, true);
        QueueFacade::setInstance(new Queue(new FileQueueStorage($this->tmpDir)));
    }

    protected function tearDown(): void {
        // Clean up
        foreach (['pending', 'processing', 'completed', 'failed'] as $dir) {
            $path = $this->tmpDir . '/' . $dir;
            if (is_dir($path)) {
                array_map('unlink', glob($path . '/*.json'));
                rmdir($path);
            }
        }
        @rmdir($this->tmpDir);
        QueueFacade::reset();
        parent::tearDown();
    }

    // ========== Job Interface ==========

    public function testSendWelcomeEmailImplementsJob() {
        $job = new SendWelcomeEmailJob('test@example.com', 'Alice');
        $this->assertInstanceOf(Job::class, $job);
        $this->assertEquals(3, $job->getMaxAttempts());
        $this->assertEquals(30, $job->getRetryDelaySeconds());
    }

    public function testGenerateReportImplementsJob() {
        $job = new GenerateReportJob(42);
        $this->assertInstanceOf(Job::class, $job);
        $this->assertEquals(2, $job->getMaxAttempts());
        $this->assertEquals(0, $job->getRetryDelaySeconds());
    }

    public function testJobProperties() {
        $job = new SendWelcomeEmailJob('user@test.com', 'Bob');
        $this->assertEquals('user@test.com', $job->getEmail());
        $this->assertEquals('Bob', $job->getName());
    }

    // ========== Dispatch ==========

    public function testDispatchReturnsId() {
        $id = QueueFacade::dispatch(new SendWelcomeEmailJob('a@b.com', 'Test'));
        $this->assertNotEmpty($id);
        $this->assertIsString($id);
    }

    public function testDispatchIncrementsPendingCount() {
        $this->assertEquals(0, QueueFacade::getPendingCount());
        QueueFacade::dispatch(new SendWelcomeEmailJob('a@b.com', 'Test'));
        $this->assertEquals(1, QueueFacade::getPendingCount());
    }

    public function testDispatchMultiple() {
        QueueFacade::dispatch(new SendWelcomeEmailJob('a@b.com', 'A'));
        QueueFacade::dispatch(new SendWelcomeEmailJob('b@b.com', 'B'));
        QueueFacade::dispatch(new GenerateReportJob(1));
        $this->assertEquals(3, QueueFacade::getPendingCount());
    }

    public function testDispatchWithPriority() {
        QueueFacade::dispatch(new SendWelcomeEmailJob('a@b.com', 'Low'), priority: 1);
        QueueFacade::dispatch(new GenerateReportJob(1), priority: 10);
        $this->assertEquals(2, QueueFacade::getPendingCount());
    }

    public function testDispatchWithDelay() {
        QueueFacade::dispatch(new SendWelcomeEmailJob('a@b.com', 'Delayed'), delaySeconds: 3600);
        $this->assertEquals(1, QueueFacade::getPendingCount());
    }

    // ========== Process ==========

    public function testProcessSuccessfulJob() {
        QueueFacade::dispatch(new SendWelcomeEmailJob('a@b.com', 'Test'));
        $processed = QueueFacade::process(10);
        $this->assertEquals(1, $processed);
        $this->assertEquals(0, QueueFacade::getPendingCount());
    }

    public function testProcessMultipleJobs() {
        QueueFacade::dispatch(new SendWelcomeEmailJob('a@b.com', 'A'));
        QueueFacade::dispatch(new SendWelcomeEmailJob('b@b.com', 'B'));
        QueueFacade::dispatch(new GenerateReportJob(5));
        $processed = QueueFacade::process(10);
        $this->assertEquals(3, $processed);
        $this->assertEquals(0, QueueFacade::getPendingCount());
    }

    public function testProcessRespectsLimit() {
        QueueFacade::dispatch(new SendWelcomeEmailJob('a@b.com', 'A'));
        QueueFacade::dispatch(new SendWelcomeEmailJob('b@b.com', 'B'));
        QueueFacade::dispatch(new SendWelcomeEmailJob('c@b.com', 'C'));
        $processed = QueueFacade::process(2);
        $this->assertEquals(2, $processed);
        $this->assertEquals(1, QueueFacade::getPendingCount());
    }

    // ========== Retry & Failure ==========

    public function testFailingJobRetriesBeforeMovingToFailed() {
        // reportId > 900 triggers failure
        QueueFacade::dispatch(new GenerateReportJob(999));

        // Attempt 1 — fails, re-queued
        QueueFacade::process(10);
        $this->assertEquals(1, QueueFacade::getPendingCount());
        $this->assertCount(0, QueueFacade::getFailed());

        // Attempt 2 — fails, max attempts reached, moved to failed
        QueueFacade::process(10);
        $this->assertEquals(0, QueueFacade::getPendingCount());
        $this->assertCount(1, QueueFacade::getFailed());
    }

    public function testFailedJobHasReason() {
        QueueFacade::dispatch(new GenerateReportJob(950));
        QueueFacade::process(10); // attempt 1
        QueueFacade::process(10); // attempt 2 — fails permanently

        $failed = QueueFacade::getFailed();
        $this->assertCount(1, $failed);
        $this->assertStringContainsString('Report service unavailable', $failed[0]->getFailReason());
    }

    public function testRetryMovesJobBackToPending() {
        QueueFacade::dispatch(new GenerateReportJob(999));
        QueueFacade::process(10);
        QueueFacade::process(10);

        $failed = QueueFacade::getFailed();
        $this->assertCount(1, $failed);

        QueueFacade::retry($failed[0]->getId());
        $this->assertEquals(1, QueueFacade::getPendingCount());
    }

    // ========== Flush ==========

    public function testFlushClearsFailedJobs() {
        QueueFacade::dispatch(new GenerateReportJob(999));
        QueueFacade::process(10);
        QueueFacade::process(10);
        $this->assertCount(1, QueueFacade::getFailed());

        QueueFacade::flush();
        $this->assertCount(0, QueueFacade::getFailed());
    }
}
