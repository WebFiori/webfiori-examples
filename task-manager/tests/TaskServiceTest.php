<?php
namespace Tests;

use App\Apis\TaskServicesManager;
use WebFiori\Http\APITestCase;

/**
 * Integration tests for the Task REST API.
 *
 * Uses {@see APITestCase} to simulate HTTP requests against the
 * {@see TaskServicesManager} without starting a web server. Each test
 * creates a fresh manager instance so the output stream is reset
 * between calls.
 *
 * These tests require a running MSSQL database with the `task-manager`
 * connection configured and migrations applied.
 */
class TaskServiceTest extends APITestCase {

    /**
     * Creates a new services manager for each API call.
     *
     * @return TaskServicesManager
     */
    private function createManager(): TaskServicesManager {
        return new TaskServicesManager();
    }

    /**
     * Verifies that GET without parameters returns all seeded tasks.
     */
    public function testListAllTasks() {
        $output = $this->getRequest($this->createManager(), 'tasks');
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);
    }

    /**
     * Verifies that GET with `status` parameter filters results correctly.
     */
    public function testListTasksByStatus() {
        $output = $this->getRequest($this->createManager(), 'tasks', [
            'status' => 'completed'
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);

        foreach ($response['data'] as $task) {
            $this->assertEquals('completed', $task['status']);
        }
    }

    /**
     * Verifies that GET with `id` returns the correct task.
     */
    public function testGetTaskById() {
        $output = $this->getRequest($this->createManager(), 'tasks', [
            'id' => 1
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals(1, $response['data'][0]['id']);
        $this->assertNotEmpty($response['data'][0]['title']);
    }

    /**
     * Verifies that GET with a non-existent ID returns a 404 error.
     */
    public function testGetTaskNotFound() {
        $output = $this->getRequest($this->createManager(), 'tasks', [
            'id' => 99999
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
        $this->assertEquals('Task not found.', $response['message']);
    }

    /**
     * Verifies that POST creates a task and returns it with an ID.
     */
    public function testCreateTask() {
        $output = $this->postRequest($this->createManager(), 'tasks', [
            'title' => 'New Test Task',
            'description' => 'Created by test'
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('New Test Task', $response['data'][0]['title']);
        $this->assertEquals('pending', $response['data'][0]['status']);
    }

    /**
     * Verifies that POST without the required `title` parameter returns an error.
     */
    public function testCreateTaskMissingTitle() {
        $output = $this->postRequest($this->createManager(), 'tasks', []);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('title', $response['message']);
        $this->assertContains('title', $response['more-info']['missing']);
    }

    /**
     * Verifies that PUT updates only the provided fields and sets `updatedAt`.
     */
    public function testUpdateTask() {
        $output = $this->putRequest($this->createManager(), 'tasks', [
            'id' => 1,
            'title' => 'Updated Title',
            'status' => 'completed'
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('Updated Title', $response['data'][0]['title']);
        $this->assertEquals('completed', $response['data'][0]['status']);
        $this->assertNotNull($response['data'][0]['updatedAt']);
    }

    /**
     * Verifies that PUT with a non-existent ID returns a 404 error.
     */
    public function testUpdateTaskNotFound() {
        $output = $this->putRequest($this->createManager(), 'tasks', [
            'id' => 99999,
            'title' => 'Does not exist'
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
    }

    /**
     * Verifies that DELETE removes the task and subsequent GET returns 404.
     */
    public function testDeleteTask() {
        // Create a task to delete
        $createOutput = $this->postRequest($this->createManager(), 'tasks', [
            'title' => 'Task to Delete'
        ]);
        $created = json_decode($createOutput, true);
        $taskId = $created['data'][0]['id'];

        $output = $this->deleteRequest($this->createManager(), 'tasks', [
            'id' => $taskId
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);

        // Verify it's gone
        $getOutput = $this->getRequest($this->createManager(), 'tasks', [
            'id' => $taskId
        ]);
        $getResponse = json_decode($getOutput, true);
        $this->assertEquals('error', $getResponse['type']);
    }

    /**
     * Verifies that DELETE with a non-existent ID returns a 404 error.
     */
    public function testDeleteTaskNotFound() {
        $output = $this->deleteRequest($this->createManager(), 'tasks', [
            'id' => 99999
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
    }

    /**
     * Verifies that PUT without the required `id` parameter returns an error.
     */
    public function testUpdateTaskMissingId() {
        $output = $this->putRequest($this->createManager(), 'tasks', [
            'title' => 'No ID provided'
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('id', $response['message']);
        $this->assertContains('id', $response['more-info']['missing']);
    }

    /**
     * Verifies that DELETE without the required `id` parameter returns an error.
     */
    public function testDeleteTaskMissingId() {
        $output = $this->deleteRequest($this->createManager(), 'tasks', []);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('id', $response['message']);
        $this->assertContains('id', $response['more-info']['missing']);
    }

    /**
     * Verifies that POST with an invalid status returns a 400 error.
     */
    public function testCreateTaskInvalidPriority() {
        $output = $this->postRequest($this->createManager(), 'tasks', [
            'title' => 'Bad Priority Task',
            'priority' => 'urgent'
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('priority', strtolower($response['message']));
    }

    /**
     * Verifies that PUT with an invalid status returns a 400 error.
     */
    public function testUpdateTaskInvalidStatus() {
        $output = $this->putRequest($this->createManager(), 'tasks', [
            'id' => 1,
            'status' => 'invalid-status'
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('status', strtolower($response['message']));
    }

    /**
     * Verifies that POST with a past due date returns a 400 error.
     */
    public function testCreateTaskPastDueDate() {
        $output = $this->postRequest($this->createManager(), 'tasks', [
            'title' => 'Past Due Task',
            'due-date' => '2020-01-01 00:00:00'
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertEquals('error', $response['type']);
        $this->assertStringContainsString('due date', strtolower($response['message']));
    }

    /**
     * Verifies that POST with a valid priority and due date creates the task.
     */
    public function testCreateTaskWithPriorityAndDueDate() {
        $futureDate = date('Y-m-d H:i:s', strtotime('+30 days'));
        $output = $this->postRequest($this->createManager(), 'tasks', [
            'title' => 'High Priority Task',
            'priority' => 'high',
            'due-date' => $futureDate
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('High Priority Task', $response['data'][0]['title']);
        $this->assertEquals('high', $response['data'][0]['priority']);
        $this->assertStringStartsWith(
            date('Y-m-d H:i:s', strtotime('+30 days')),
            $response['data'][0]['dueDate']
        );
    }

    /**
     * Verifies that GET with pagination returns paginated results with metadata.
     */
    public function testGetTasksPaginated() {
        $output = $this->getRequest($this->createManager(), 'tasks', [
            'page' => 1,
            'per-page' => 2
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $data = $response['data'];
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('perPage', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('totalPages', $data);
        $this->assertEquals(1, $data['page']);
        $this->assertEquals(2, $data['perPage']);
        $this->assertCount(2, $data['items']);
    }

    /**
     * Verifies that GET with page 2 returns different results.
     */
    public function testGetTasksPaginatedPage2() {
        $output = $this->getRequest($this->createManager(), 'tasks', [
            'page' => 2,
            'per-page' => 2
        ]);
        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $data = $response['data'];
        $this->assertEquals(2, $data['page']);
        $this->assertNotEmpty($data['items']);
    }
}
