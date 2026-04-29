<?php
namespace App\Apis;

use App\AppCache;
use App\Domain\ShortLink;
use App\Infrastructure\Repository\ShortLinkRepository;

use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\DeleteMapping;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * REST controller for short link CRUD operations.
 */
#[RestController('links', 'URL shortener API')]
class ShortLinkService extends WebService {
    /**
     * Creates a new short link. Returns existing link if URL was already shortened.
     */
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'url', type: ParamType::URL, description: 'URL to shorten')]
    #[RequestParam(name: 'expiresAt', type: ParamType::STRING, optional: true, description: 'Expiration datetime (Y-m-d H:i:s)')]
    public function createLink(?string $url = null, ?string $expiresAt = null): array {
        $repo = $this->getRepo();

        // Return existing if already shortened
        $existing = $repo->findByRedirectTo($url);

        if ($existing !== null) {
            return [$existing];
        }

        $link = new ShortLink(
            id: $repo->generateShortId(),
            redirectTo: trim($url),
            ipAddress: $_SERVER['REMOTE_ADDR'] ?? '',
            createdAt: date('Y-m-d H:i:s'),
            expiresAt: $expiresAt
        );
        $repo->insert($link);

        // Cache the new link
        AppCache::get()->set('link:'.$link->id, $link, 300);

        return [$link];
    }

    /**
     * Deletes a short link and invalidates its cache.
     */
    #[DeleteMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::STRING, description: 'Short link ID')]
    public function deleteLink(?string $id = null): array {
        $repo = $this->getRepo();
        $link = $repo->findById($id);

        if ($link === null) {
            throw new NotFoundException('Link not found.');
        }

        $repo->deleteById($id);
        AppCache::get()->delete('link:'.$id);

        return [$link];
    }
    /**
     * Lists all links or returns a single link by ID.
     */
    #[GetMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'id', type: ParamType::STRING, optional: true, description: 'Short link ID')]
    public function getLinks(?string $id = null): array {
        $repo = $this->getRepo();

        if ($id !== null) {
            $link = $repo->findById($id);

            if ($link === null) {
                throw new NotFoundException('Link not found.');
            }

            return [$link];
        }

        return $repo->findAll();
    }

    private function getRepo(): ShortLinkRepository {
        return new ShortLinkRepository(new Database(App::getConfig()->getDBConnection('shortener')));
    }
}
