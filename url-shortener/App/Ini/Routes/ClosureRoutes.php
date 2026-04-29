<?php
namespace App\Ini\Routes;

use App\AppCache;
use App\Infrastructure\Repository\ShortLinkRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

class ClosureRoutes {
    public static function create() {
        /**
         * Redirect route: /{code} -> original URL.
         * Checks cache first, falls back to DB. Increments click count.
         */
        Router::closure([
            RouteOption::PATH => '/{code}',
            RouteOption::TO => function () {
                $code = Router::getParameterValue('code');
                $db = new Database(App::getConfig()->getDBConnection('shortener'));
                $repo = new ShortLinkRepository($db);
                $cache = AppCache::get();

                // Try cache first
                $link = $cache->get('link:'.$code, function () use ($repo, $code) {
                    return $repo->findById($code);
                }, 300);

                if ($link !== null && ($link->expiresAt === null || $link->expiresAt > date('Y-m-d H:i:s'))) {
                    $repo->incrementClicks($code);
                    App::getResponse()->setCode(301);
                    App::getResponse()->addHeader('location', $link->redirectTo);
                } else {
                    $cache->delete('link:'.$code);
                    App::getResponse()->setCode(302);
                    App::getResponse()->addHeader('location', App::getConfig()->getBaseURL());
                }
            },
        ]);
    }
}
