<?php
namespace App\Apis;

use App\Domain\Post;
use App\Infrastructure\Repository\PostRepository;
use WebFiori\Cache\CacheFacade;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
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
use WebFiori\Http\RequestMethod;
use WebFiori\Http\WebService;

/**
 * REST controller for blog post CRUD operations.
 *
 * GET endpoints are public (`#[AllowAnonymous]`). POST/PUT/DELETE require
 * an active session, enforced by `isAuthorized()`.
 *
 * Published post listings are cached for 120 seconds to reduce database load.
 */
#[RestController('posts', 'Blog posts API')]
class PostService extends WebService {
    private const CACHE_TTL = 120;

    /**
     * Creates a new blog post. Requires authentication.
     */
    #[PostMapping]
    #[ResponseBody]
    #[RequestParam(name: 'title', type: ParamType::STRING, description: 'Post title')]
    #[RequestParam(name: 'slug', type: ParamType::STRING, description: 'URL slug')]
    #[RequestParam(name: 'content', type: ParamType::STRING, optional: true, default: '', description: 'Post content')]
    #[RequestParam(name: 'categoryId', type: ParamType::INT, optional: true, description: 'Category ID')]
    #[RequestParam(name: 'status', type: ParamType::STRING, optional: true, default: 'draft', description: 'draft or published')]
    public function createPost(?string $title = null, ?string $slug = null, ?string $content = null, ?int $categoryId = null, ?string $status = null): array {
        $repo = $this->getRepo();
        $authorId = (int) SessionsManager::get('author-id');

        $post = new Post(
            title: $title,
            slug: $slug,
            content: $content ?? '',
            authorId: $authorId,
            categoryId: $categoryId,
            status: $status ?? 'draft',
            createdAt: date('Y-m-d H:i:s')
        );

        $repo->save($post);
        $this->invalidateCache();

        return [$post];
    }

    /**
     * Deletes a post. Requires authentication.
     */
    #[DeleteMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Post ID')]
    public function deletePost(?int $id = null): array {
        $repo = $this->getRepo();
        $post = $repo->findById($id);

        if ($post === null) {
            throw new NotFoundException('Post not found.');
        }

        $repo->deleteById($post->id);
        $this->invalidateCache();

        return [$post];
    }

    /**
     * Lists published posts (paginated) or returns a single post by ID.
     *
     * Paginated listings are served from cache when available.
     */
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true, description: 'Post ID')]
    #[RequestParam(name: 'page', type: ParamType::INT, optional: true, default: 1, description: 'Page number')]
    #[RequestParam(name: 'perPage', type: ParamType::INT, optional: true, default: 5, description: 'Items per page')]
    #[RequestParam(name: 'categoryId', type: ParamType::INT, optional: true, description: 'Filter by category')]
    public function getPosts(?int $id = null, ?int $page = null, ?int $perPage = null, ?int $categoryId = null): array {
        $repo = $this->getRepo();

        if ($id !== null) {
            $post = $repo->findByIdWithDetails($id);

            if ($post === null) {
                throw new NotFoundException('Post not found.');
            }

            return [$post];
        }

        $page = $page ?? 1;
        $perPage = $perPage ?? 5;

        $cacheKey = "posts:list:p{$page}:pp{$perPage}:c" . ($categoryId ?? 'all');

        $items = CacheFacade::get($cacheKey, function () use ($repo, $page, $perPage, $categoryId) {
            $result = $repo->findPublished($page, $perPage, $categoryId);

            return $result['items'];
        }, self::CACHE_TTL);

        return $items;
    }

    /**
     * Checks if the current request is authorized.
     *
     * Returns true for GET requests (public) and for authenticated sessions.
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

    /**
     * Updates an existing post. Requires authentication.
     */
    #[PutMapping]
    #[ResponseBody]
    #[RequestParam(name: 'id', type: ParamType::INT, description: 'Post ID')]
    #[RequestParam(name: 'title', type: ParamType::STRING, optional: true)]
    #[RequestParam(name: 'slug', type: ParamType::STRING, optional: true)]
    #[RequestParam(name: 'content', type: ParamType::STRING, optional: true)]
    #[RequestParam(name: 'categoryId', type: ParamType::INT, optional: true)]
    #[RequestParam(name: 'status', type: ParamType::STRING, optional: true)]
    public function updatePost(?int $id = null, ?string $title = null, ?string $slug = null, ?string $content = null, ?int $categoryId = null, ?string $status = null): array {
        $repo = $this->getRepo();
        $post = $repo->findById($id);

        if ($post === null) {
            throw new NotFoundException('Post not found.');
        }

        foreach (['title', 'slug', 'content', 'status'] as $field) {
            $val = $$field;

            if ($val !== null) {
                $post->$field = $val;
            }
        }

        if ($categoryId !== null) {
            $post->categoryId = $categoryId;
        }

        $post->updatedAt = date('Y-m-d H:i:s');
        $repo->save($post);
        $this->invalidateCache();

        return [$post];
    }

    /**
     * Invalidates the posts list cache after mutations.
     */
    private function invalidateCache(): void {
        CacheFacade::flush();
    }

    private function getRepo(): PostRepository {
        return new PostRepository(new Database(App::getConfig()->getDBConnection('blog')));
    }
}
