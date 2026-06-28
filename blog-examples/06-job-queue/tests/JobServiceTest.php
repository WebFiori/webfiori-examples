<?php
namespace Tests;

use App\Apis\DispatchEmailService;
use App\Apis\DispatchReportService;
use App\Apis\QueueStatusService;
use WebFiori\Http\Test\ServiceTestCase;
use WebFiori\Queue\FileQueueStorage;
use WebFiori\Queue\Queue;
use WebFiori\Queue\QueueFacade;

class JobServiceTest extends ServiceTestCase {
    private string $tmpDir;

    protected function setUp(): void {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir() . '/wf-api-queue-' . uniqid();
        mkdir($this->tmpDir, 0755, true);
        QueueFacade::setInstance(new Queue(new FileQueueStorage($this->tmpDir)));
    }

    protected function tearDown(): void {
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

    public function testDispatchEmailReturnsJobId() {
        $response = $this->post(new DispatchEmailService(), [
            'email' => 'alice@example.com',
            'name' => 'Alice',
        ]);

        $response->assertOk()->assertJson();
        $json = $response->getJson();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('job_id', $json['data']);
        $this->assertEquals('queued', $json['data']['status']);
    }

    public function testDispatchEmailIncreasesQueue() {
        $this->assertEquals(0, QueueFacade::getPendingCount());

        $this->post(new DispatchEmailService(), [
            'email' => 'bob@example.com',
            'name' => 'Bob',
        ]);

        $this->assertEquals(1, QueueFacade::getPendingCount());
    }

    public function testDispatchReportReturnsJobIdAndPriority() {
        $response = $this->post(new DispatchReportService(), [
            'report-id' => 42,
        ]);

        $response->assertOk()->assertJson();
        $json = $response->getJson();
        $this->assertArrayHasKey('job_id', $json['data']);
        $this->assertEquals('queued', $json['data']['status']);
        $this->assertEquals(5, $json['data']['priority']);
    }

    public function testStatusEndpoint() {
        $response = $this->get(new QueueStatusService());

        $response->assertOk()->assertJson();
        $json = $response->getJson();
        $this->assertEquals(0, $json['data']['pending']);
        $this->assertEquals(0, $json['data']['failed']);
    }

    public function testStatusReflectsDispatchedJobs() {
        $this->post(new DispatchEmailService(), ['email' => 'a@b.com', 'name' => 'A']);
        $this->post(new DispatchEmailService(), ['email' => 'b@b.com', 'name' => 'B']);

        $response = $this->get(new QueueStatusService());

        $json = $response->getJson();
        $this->assertEquals(2, $json['data']['pending']);
        $this->assertEquals(0, $json['data']['failed']);
    }
}
