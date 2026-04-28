<?php
namespace App\Apis;

use App\Domain\Category;
use App\Infrastructure\Repository\CategoryRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\ParamType;
use WebFiori\Http\RequestMethod;
use WebFiori\Http\WebService;

/**
 * REST controller for blog categories.
 */
#[RestController('categories', 'Blog categories API')]
class CategoryService extends WebService {
    /**
     * Creates a new category. Requires authentication.
     */
    #[PostMapping]
    #[ResponseBody]
    #[RequestParam(name: 'name', type: ParamType::STRING, description: 'Category name')]
    #[RequestParam(name: 'slug', type: ParamType::STRING, description: 'URL slug')]
    #[RequestParam(name: 'description', type: ParamType::STRING, optional: true, default: '')]
    public function createCategory(?string $name = null, ?string $slug = null, ?string $description = null): array {
        $repo = $this->getRepo();
        $cat = new Category(
            name: $name,
            slug: $slug,
            description: $description ?? ''
        );
        $repo->save($cat);

        return [$cat];
    }

    /**
     * Lists all categories.
     */
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    public function getCategories(): array {
        return $this->getRepo()->findAll();
    }

    /**
     * GET is public; POST requires auth.
     */
    public function isAuthorized(): bool {
        $method = $this->getManager()?->getRequest()?->getMethod() ?? '';

        if ($method === RequestMethod::GET) {
            return true;
        }

        $session = SessionsManager::getActiveSession();

        if ($session === null) {
            return false;
        }

        return $session->get('author-id') !== null;
    }

    private function getRepo(): CategoryRepository {
        return new CategoryRepository(new Database(App::getConfig()->getDBConnection('blog')));
    }
}
