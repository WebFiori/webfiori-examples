<?php
namespace Tests;

use App\Apis\TicketServicesManager;
use WebFiori\Http\APITestCase;

/**
 * Tests for the Reply REST API endpoint.
 */
class ReplyServiceTest extends APITestCase {
    public function testAddReply() {
        $output = $this->postRequest($this->mgr(), 'replies', [
            'ticketId' => 1,
            'authorName' => 'Tester',
            'content' => 'Test reply',
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('Test reply', $response['data'][0]['content']);
    }

    public function testAddReplyMissingContent() {
        $output = $this->postRequest($this->mgr(), 'replies', [
            'ticketId' => 1,
            'authorName' => 'Tester',
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('content', $response['message']);
    }

    public function testAddReplyMissingTicketId() {
        $output = $this->postRequest($this->mgr(), 'replies', [
            'authorName' => 'Tester',
            'content' => 'Hello',
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('ticketId', $response['message']);
    }

    public function testAddReplyToNonExistentTicket() {
        $output = $this->postRequest($this->mgr(), 'replies', [
            'ticketId' => 99999,
            'authorName' => 'Tester',
            'content' => 'Hello',
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertEquals('Ticket not found.', $response['message']);
    }
    private function mgr(): TicketServicesManager {
        return new TicketServicesManager();
    }
}
