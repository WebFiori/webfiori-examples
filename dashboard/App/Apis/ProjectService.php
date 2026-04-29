<?php
namespace App\Apis;

use App\Domain\Project;
use App\Infrastructure\Repository\ProjectRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Annotations\DeleteMapping;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\PutMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\RequestMethod;
use WebFiori\Http\WebService;

/**
 * Project management API. GET requires VIEW_PROJECTS; write operations require CREATE_PROJECT.
 */
#[RestController('projects', 'Project management — list, create, update, and delete projects.')]
class ProjectService extends WebService {
    public function isAuthorized(): bool {
        SessionsManager::start('wf-session');
        $privileges = SessionsManager::get('user-privileges') ?? [];
        $method = $this->getManager()?->getRequest()?->getMethod() ?? '';

        if ($method === RequestMethod::GET) {
            return in_array('VIEW_PROJECTS', $privileges);
        }

        return in_array('CREATE_PROJECT', $privileges);
    }

    /**
     * Lists all projects with owner names, or returns a single project by ID.
     */
    #[GetMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true, description: 'Project ID. If omitted, all projects are returned.')]
    public function getProjects(?int $id = null): array {
        $repo = $this->getRepo();

        if ($id !== null) {
            $project = $repo->findByIdWithOwner($id);

            if ($project === null) {
                throw new NotFoundException('Project not found.');
            }

            return [$project];
        }

        return $repo->findAllWithOwner();
    }

    /**
     * Creates a new project owned by the current user.
     */
    #[PostMapping]
    #[ResponseBody]
    #[RequestParam(name: 'name', type: ParamType::STRING, description: 'Project name.')]
    #[RequestParam(name: 'description', type: ParamType::STRING, optional: true, default: '', description: 'Project description.')]
    #[RequestParam(name: 'status', type: ParamType::STRING, optional: true, default: 'active', description: 'Status: active, completed, or archived.')]
    public function createProject(?string $name = null, ?string $description = null, ?string $status = null): array {
        $project = new Project(
            name: $name,
            description: $description ?? '',
            status: $status ?? 'active',
            ownerId: (int) SessionsManager::get('user-id'),
            createdAt: date('Y-m-d H:i:s')
        );
        $this->getRepo()->save($project);

        return [$project];
    }

    /**
     * Updates an existing project's name, description, or status.
     */
    #[PutMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'ID of the project to update.')]
    #[RequestParam(name: 'name', type: ParamType::STRING, optional: true, description: 'New project name.')]
    #[RequestParam(name: 'description', type: ParamType::STRING, optional: true, description: 'New description.')]
    #[RequestParam(name: 'status', type: ParamType::STRING, optional: true, description: 'New status: active, completed, or archived.')]
    public function updateProject(?int $id = null, ?string $name = null, ?string $description = null, ?string $status = null): array {
        $repo = $this->getRepo();
        $project = $repo->findById($id);

        if ($project === null) {
            throw new NotFoundException('Project not found.');
        }

        if ($name !== null) {
            $project->name = $name;
        }

        if ($description !== null) {
            $project->description = $description;
        }

        if ($status !== null) {
            $project->status = $status;
        }

        $project->updatedAt = date('Y-m-d H:i:s');
        $repo->save($project);

        return [$project];
    }

    /**
     * Permanently deletes a project.
     */
    #[DeleteMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'ID of the project to delete.')]
    public function deleteProject(?int $id = null): array {
        $repo = $this->getRepo();
        $project = $repo->findById($id);

        if ($project === null) {
            throw new NotFoundException('Project not found.');
        }

        $repo->deleteById($id);

        return [$project];
    }

    private function getRepo(): ProjectRepository {
        return new ProjectRepository(new Database(App::getConfig()->getDBConnection('dashboard')));
    }
}
