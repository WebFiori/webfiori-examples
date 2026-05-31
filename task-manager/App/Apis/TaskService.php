<?php
namespace App\Apis;

use App\Domain\Task;
use App\Infrastructure\Repository\TaskRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\DeleteMapping;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\PutMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\BadRequestException;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * REST controller for task CRUD operations.
 *
 * Each public method is mapped to an HTTP verb via PHP 8 attributes.
 * The `#[ResponseBody]` attribute tells the framework to automatically
 * serialize the return value as JSON. Errors are signalled by throwing
 * {@see NotFoundException}, which the framework catches and converts
 * to a JSON error response.
 */
#[RestController('tasks', 'Task management API')]
class TaskService extends WebService {

    /**
     * Retrieves tasks with optional filtering and pagination.
     *
     * - If `id` is provided, returns a single task or throws 404.
     * - If `page` is provided, returns paginated results.
     * - If `status` is provided, returns tasks filtered by that status.
     * - Otherwise, returns all tasks.
     *
     * @return array Response data (tasks or paginated result).
     *
     * @throws NotFoundException If a specific task ID is requested but does not exist.
     */
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true, description: 'Task ID')]
    #[RequestParam(name: 'status', type: ParamType::STRING, optional: true, description: 'Filter by status (pending, in-progress, or completed)')]
    #[RequestParam(name: 'page', type: ParamType::INT, optional: true, description: 'Page number (1-based) for pagination')]
    #[RequestParam(name: 'per-page', type: ParamType::INT, optional: true, description: 'Items per page (default: 10, max: 100)')]
    public function getTasks(): array {
        $repo = $this->getRepo();
        $id = $this->getParamVal('id');

        if ($id !== null) {
            $task = $repo->findById($id);

            if ($task === null) {
                throw new NotFoundException('Task not found.');
            }

            return [$task];
        }

        $status = $this->getParamVal('status');
        $page = $this->getParamVal('page');

        if ($page !== null) {
            $perPage = min($this->getParamVal('per-page') ?? 10, 100);
            $result = $repo->paginate($page, $perPage, ['id' => 'a']);

            return [
                'items' => $result->getItems(),
                'page' => $result->getCurrentPage(),
                'perPage' => $result->getPerPage(),
                'total' => $result->getTotalItems(),
                'totalPages' => $result->getTotalPages(),
            ];
        }

        if ($status !== null) {
            return $repo->findByStatus($status);
        }

        return $repo->findAll();
    }

    /**
     * Creates a new task with validation.
     *
     * Validates that `status` is one of the allowed values and that
     * `dueDate` (if provided) is in the future.
     *
     * @return Task[] Single-element array containing the created task.
     */
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'title', type: ParamType::STRING, description: 'Task title')]
    #[RequestParam(name: 'description', type: ParamType::STRING, optional: true, default: '', description: 'Task description')]
    #[RequestParam(name: 'priority', type: ParamType::STRING, optional: true, default: 'medium', description: 'Priority: low, medium, or high')]
    #[RequestParam(name: 'due-date', type: ParamType::STRING, optional: true, description: 'Due date (Y-m-d H:i:s format, must be in the future)')]
    public function createTask(): array {
        $priority = $this->getParamVal('priority') ?? 'medium';
        $this->validatePriority($priority);

        $dueDate = $this->getParamVal('due-date');
        $this->validateDueDate($dueDate);

        $task = new Task(
            title: $this->getParamVal('title'),
            description: $this->getParamVal('description') ?? '',
            priority: $priority,
            dueDate: $dueDate,
            createdAt: date('Y-m-d H:i:s')
        );

        $repo = $this->getRepo();
        $repo->save($task);

        $created = $repo->findLastByTitle($task->title, $task->createdAt);

        return [$created ?? $task];
    }

    /**
     * Updates an existing task with validation.
     *
     * Only the fields that are provided in the request are updated;
     * omitted fields retain their current values. The `updated_at`
     * timestamp is set automatically.
     *
     * @return Task[] Single-element array containing the updated task.
     *
     * @throws NotFoundException If the task with the given ID does not exist.
     */
    #[PutMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Task ID')]
    #[RequestParam(name: 'title', type: ParamType::STRING, optional: true, description: 'New title')]
    #[RequestParam(name: 'description', type: ParamType::STRING, optional: true, description: 'New description')]
    #[RequestParam(name: 'status', type: ParamType::STRING, optional: true, description: 'New status: pending, in-progress, or completed')]
    #[RequestParam(name: 'priority', type: ParamType::STRING, optional: true, description: 'New priority: low, medium, or high')]
    #[RequestParam(name: 'due-date', type: ParamType::STRING, optional: true, description: 'New due date (Y-m-d H:i:s format)')]
    public function updateTask(): array {
        $repo = $this->getRepo();
        $id = $this->getParamVal('id');
        $task = $repo->findById($id);

        if ($task === null) {
            throw new NotFoundException('Task not found.');
        }

        $title = $this->getParamVal('title');
        $description = $this->getParamVal('description');
        $status = $this->getParamVal('status');
        $priority = $this->getParamVal('priority');
        $dueDate = $this->getParamVal('due-date');

        if ($status !== null) {
            $this->validateStatus($status);
            $task->status = $status;
        }

        if ($priority !== null) {
            $this->validatePriority($priority);
            $task->priority = $priority;
        }

        if ($dueDate !== null) {
            $this->validateDueDate($dueDate);
            $task->dueDate = $dueDate;
        }

        if ($title !== null) {
            $task->title = $title;
        }

        if ($description !== null) {
            $task->description = $description;
        }

        $task->updatedAt = date('Y-m-d H:i:s');
        $repo->save($task);

        return [$task];
    }

    /**
     * Deletes a task by ID.
     *
     * Returns the deleted task data in the response so the client can
     * confirm which record was removed.
     *
     * @return Task[] Single-element array containing the deleted task.
     *
     * @throws NotFoundException If the task with the given ID does not exist.
     */
    #[DeleteMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Task ID')]
    public function deleteTask(): array {
        $repo = $this->getRepo();
        $id = $this->getParamVal('id');
        $task = $repo->findById($id);

        if ($task === null) {
            throw new NotFoundException('Task not found.');
        }

        $repo->deleteById($id);

        return [$task];
    }

    /**
     * Validates that the status value is one of the allowed values.
     */
    private function validateStatus(string $status): void {
        if (!in_array($status, Task::VALID_STATUSES, true)) {
            throw new BadRequestException(
                'Invalid status. Allowed values: ' . implode(', ', Task::VALID_STATUSES) . '.'
            );
        }
    }

    /**
     * Validates that the priority value is one of the allowed values.
     */
    private function validatePriority(string $priority): void {
        if (!in_array($priority, Task::VALID_PRIORITIES, true)) {
            throw new BadRequestException(
                'Invalid priority. Allowed values: ' . implode(', ', Task::VALID_PRIORITIES) . '.'
            );
        }
    }

    /**
     * Validates that the due date is a valid datetime string in the future.
     */
    private function validateDueDate(?string $dueDate): void {
        if ($dueDate === null) {
            return;
        }

        $timestamp = strtotime($dueDate);

        if ($timestamp === false) {
            throw new BadRequestException('Invalid due date format. Use Y-m-d H:i:s.');
        }

        if ($timestamp <= time()) {
            throw new BadRequestException('Due date must be in the future.');
        }
    }

    /**
     * Creates a TaskRepository instance using the configured database connection.
     *
     * @return TaskRepository
     */
    private function getRepo(): TaskRepository {
        $db = new Database(App::getConfig()->getDBConnection('task-manager'));

        return new TaskRepository($db);
    }
}
