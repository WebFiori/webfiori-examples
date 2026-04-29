<?php
namespace Tests;

use App\Apis\TicketServicesManager;
use WebFiori\Http\APITestCase;

/**
 * Tests for the Attachment REST API endpoint.
 */
class AttachmentServiceTest extends APITestCase {
    public function testDownloadNonExistentAttachment() {
        $output = $this->getRequest($this->mgr(), 'attachments', ['id' => 99999]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertEquals('Attachment not found.', $response['message']);
    }

    public function testUploadMissingTicketId() {
        $output = $this->postRequest($this->mgr(), 'attachments', []);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('ticketId', $response['message']);
    }
    private function mgr(): TicketServicesManager {
        return new TicketServicesManager();
    }
}
