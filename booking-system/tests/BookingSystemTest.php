<?php
namespace Tests;

use App\Apis\BookingServicesManager;
use App\Domain\Appointment;
use App\Domain\User;
use App\Health\DatabaseCheck;
use App\Health\SmsProviderCheck;
use App\Policies\AppointmentCancelPolicy;
use App\Policies\AppointmentViewPolicy;
use App\Services\MockSmsNotifier;
use WebFiori\Framework\Access;
use WebFiori\Http\APITestCase;
use WebFiori\Http\SecurityContext;
use WebFiori\Queue\QueueFacade;

class BookingSystemTest extends APITestCase {
    private ?User $currentUser = null;

    protected function setUp(): void {
        parent::setUp();
        QueueFacade::flush();
    }

    protected function tearDown(): void {
        SecurityContext::clear();
        parent::tearDown();
    }

    // --- Auth ---

    public function testLoginSuccess() {
        $output = $this->postRequest($this->mgr(), 'auth', [
            'email' => 'alice@example.com', 'password' => 'alice123',
        ]);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('patient', $response['data']['role']);
    }

    public function testLoginFails() {
        $output = $this->postRequest($this->mgr(), 'auth', [
            'email' => 'alice@example.com', 'password' => 'wrong',
        ]);
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    // --- Services Catalog ---

    public function testListServices() {
        $output = $this->getRequest($this->mgr(), 'services');
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);
    }

    // --- Appointments ---

    public function testBookAppointment() {
        $this->loginAs('patient');
        $future = date('Y-m-d H:i:s', strtotime('+3 days 10:00'));
        $output = $this->postRequest($this->mgr(), 'appointments', [
            'providerId' => 2, 'serviceId' => 1, 'startTime' => $future,
        ], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('booked', $response['data'][0]['status']);
    }

    public function testBookAppointmentQueuesNotification() {
        $this->loginAs('patient');
        $future = date('Y-m-d H:i:s', strtotime('+4 days 14:00'));
        $this->postRequest($this->mgr(), 'appointments', [
            'providerId' => 2, 'serviceId' => 2, 'startTime' => $future,
        ], [], $this->currentUser);
        $this->assertGreaterThan(0, QueueFacade::getPendingCount());
    }

    public function testListOwnAppointments() {
        $this->loginAs('patient');
        $output = $this->getRequest($this->mgr(), 'appointments', [], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testProviderSeesOwnSchedule() {
        $this->loginAs('provider');
        $output = $this->getRequest($this->mgr(), 'appointments', [], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    public function testCancelAppointment() {
        $this->loginAs('patient');
        // Book then cancel
        $future = date('Y-m-d H:i:s', strtotime('+5 days 11:00'));
        $this->postRequest($this->mgr(), 'appointments', [
            'providerId' => 2, 'serviceId' => 1, 'startTime' => $future,
        ], [], $this->currentUser);

        $listOutput = $this->getRequest($this->mgr(), 'appointments', [], [], $this->currentUser);
        $appts = json_decode($listOutput, true)['data'];
        $last = end($appts);

        $output = $this->deleteRequest($this->mgr(), 'appointments', ['id' => $last['id']], [], $this->currentUser);
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('cancelled', $response['data'][0]['status']);
    }

    public function testRequiresAuth() {
        SecurityContext::clear();
        $output = $this->getRequest($this->mgr(), 'appointments');
        $response = json_decode($output, true);
        $this->assertEquals('error', $response['type']);
    }

    // --- Policies ---

    public function testViewPolicyAllowsOwner() {
        $user = new User(id: 4, role: 'patient');
        $appt = new Appointment(patientId: 4, providerId: 2);
        $this->assertTrue((new AppointmentViewPolicy())->evaluate($user, $appt));
    }

    public function testViewPolicyDeniesNonOwner() {
        $user = new User(id: 4, role: 'patient');
        $appt = new Appointment(patientId: 99, providerId: 2);
        $this->assertFalse((new AppointmentViewPolicy())->evaluate($user, $appt));
    }

    public function testViewPolicyAllowsProvider() {
        $user = new User(id: 2, role: 'provider');
        $appt = new Appointment(patientId: 4, providerId: 2);
        $this->assertTrue((new AppointmentViewPolicy())->evaluate($user, $appt));
    }

    public function testCancelPolicyDeniesCompleted() {
        $user = new User(id: 4, role: 'patient');
        $appt = new Appointment(patientId: 4, status: 'completed');
        $this->assertFalse((new AppointmentCancelPolicy())->evaluate($user, $appt));
    }

    // --- Health ---

    public function testDatabaseHealth() {
        $this->assertEquals('ok', (new DatabaseCheck())->check()->getStatus());
    }

    public function testSmsProviderHealth() {
        $this->assertEquals('ok', (new SmsProviderCheck())->check()->getStatus());
    }

    public function testHealthEndpoint() {
        $output = $this->getRequest($this->mgr(), 'health');
        $response = json_decode($output, true);
        $this->assertArrayHasKey('data', $response);
    }

    // --- Services ---

    public function testMockSmsNotifier() {
        $notifier = new MockSmsNotifier();
        $this->assertTrue($notifier->sendSms('+1555000001', 'Test'));
    }

    // --- Helpers ---

    private function loginAs(string $role): void {
        $map = ['admin' => [1, 'Admin'], 'provider' => [2, 'Dr. Smith'], 'patient' => [4, 'Alice Patient']];
        [$id, $name] = $map[$role];
        $this->currentUser = new User(id: $id, name: $name, role: $role);
        Access::assignRoleToUser($id, $role);
    }

    private function mgr(): BookingServicesManager {
        return new BookingServicesManager();
    }
}
