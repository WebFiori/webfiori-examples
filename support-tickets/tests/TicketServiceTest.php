<?php
namespace Tests;

use App\Apis\TicketServicesManager;
use WebFiori\Http\APITestCase;

/**
 * Tests for the Ticket REST API endpoints.
 */
class TicketServiceTest extends APITestCase {
    public function testCreateTicket() {
        $output = $this->postRequest($this->mgr(), 'tickets', [
            'subject' => 'Test ticket',
            'description' => 'Test description',
            'submitterName' => 'Tester',
            'submitterEmail' => 'tester@example.com',
            'priority' => 'low',
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('Test ticket', $response['data'][0]['subject']);
    }

    public function testCreateTicketMissingEmail() {
        $output = $this->postRequest($this->mgr(), 'tickets', [
            'subject' => 'Test',
            'description' => 'Test',
            'submitterName' => 'X',
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('submitterEmail', $response['message']);
    }

    public function testCreateTicketMissingSubject() {
        $output = $this->postRequest($this->mgr(), 'tickets', [
            'description' => 'No subject',
            'submitterName' => 'X',
            'submitterEmail' => 'x@example.com',
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('subject', $response['message']);
    }

    public function testFilterByEmail() {
        $output = $this->getRequest($this->mgr(), 'tickets', ['email' => 'alice@example.com']);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testFilterByStatus() {
        $output = $this->getRequest($this->mgr(), 'tickets', ['status' => 'open']);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testGetTicketById() {
        $output = $this->getRequest($this->mgr(), 'tickets', ['id' => 1]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testGetTicketNotFound() {
        $output = $this->getRequest($this->mgr(), 'tickets', ['id' => 99999]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertEquals('Ticket not found.', $response['message']);
    }

    public function testListAllTickets() {
        $output = $this->getRequest($this->mgr(), 'tickets');
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
    }

    public function testUpdateStatus() {
        $output = $this->putRequest($this->mgr(), 'tickets', [
            'id' => 1,
            'status' => 'in-progress',
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('in-progress', $response['data'][0]['status']);
    }

    public function testUpdateStatusMissingId() {
        $output = $this->putRequest($this->mgr(), 'tickets', ['status' => 'closed']);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('id', $response['message']);
    }

    public function testUpdateStatusNotFound() {
        $output = $this->putRequest($this->mgr(), 'tickets', [
            'id' => 99999,
            'status' => 'closed',
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }
    private function mgr(): TicketServicesManager {
        return new TicketServicesManager();
    }
}
