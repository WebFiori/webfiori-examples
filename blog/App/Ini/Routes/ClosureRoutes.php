<?php
namespace App\Ini\Routes;

use WebFiori\Framework\App;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Session\SessionsManager;

class ClosureRoutes {
    public static function create() {
        /**
         * Logs out the current user and redirects to home.
         */
        Router::closure([
            RouteOption::PATH => '/do-logout',
            RouteOption::MIDDLEWARE => ['start-session'],
            RouteOption::TO => function ()
            {
                SessionsManager::start('wf-session');
                SessionsManager::destroy();

                App::getResponse()->addHeader('Location', App::getConfig()->getBaseURL().'/');
                App::getResponse()->setCode(302);
            }
        ]);
    }
}
