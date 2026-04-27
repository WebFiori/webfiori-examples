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
     * Retrieves tasks.
     *
     * - If `id` is provided, returns a single task or throws 404.
     * - If `status` is provided, returns tasks filtered by that status.
     * - Otherwise, returns all tasks.
     *
     * @return Task[] Array of Task entities serialized to JSON by the framework.
     *
     * @throws NotFoundException If a specific task ID is requested but does not exist.
     */
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true, description: 'Task ID')]
    #[RequestParam(name: 'status', type: ParamType::STRING, optional: true, description: 'Filter by status (pending or completed)')]
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

        if ($status !== null) {
            return $repo->findByStatus($status);
        }

        return $repo->findAll();
    }

    /**
     * Creates a new task.
     *
     * Inserts the task into the database, then queries it back to include
     * the auto-generated ID in the response.
     *
     * @return Task[] Single-element array containing the created task.
     */
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'title', type: ParamType::STRING, description: 'Task title')]
    #[RequestParam(name: 'description', type: ParamType::STRING, optional: true, default: '', description: 'Task description')]
    public function createTask(): array {
        $task = new Task(
            title: $this->getParamVal('title'),
            description: $this->getParamVal('description') ?? '',
            createdAt: date('Y-m-d H:i:s')
        );

        $repo = $this->getRepo();
        $repo->save($task);

        $created = $repo->findLastByTitle($task->title, $task->createdAt);

        return [$created ?? $task];
    }

    /**
     * Updates an existing task.
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
    #[RequestParam(name: 'status', type: ParamType::STRING, optional: true, description: 'New status (pending or completed)')]
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

        if ($title !== null) {
            $task->title = $title;
        }

        if ($description !== null) {
            $task->description = $description;
        }

        if ($status !== null) {
            $task->status = $status;
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
     * Creates a TaskRepository instance using the configured database connection.
     *
     * @return TaskRepository
     */
    private function getRepo(): TaskRepository {
        $db = new Database(App::getConfig()->getDBConnection('task-manager'));

        return new TaskRepository($db);
    }
}
