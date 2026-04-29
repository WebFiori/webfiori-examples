<?php
namespace App\Ini\Routes;

use WebFiori\Framework\App;
use WebFiori\Framework\Middleware\StartSessionMiddleware;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Session\SessionsManager;

class ClosureRoutes {
    public static function create() {
        Router::redirect('/', '/dashboard');

        Router::closure([
            RouteOption::PATH => '/do-logout',
            RouteOption::MIDDLEWARE => [StartSessionMiddleware::class],
            RouteOption::TO => function ()
            {
                SessionsManager::start('wf-session');
                SessionsManager::destroy();
                App::getResponse()->addHeader('Location', App::getConfig()->getBaseURL().'/login');
                App::getResponse()->setCode(302);
            },
        ]);
    }
}
