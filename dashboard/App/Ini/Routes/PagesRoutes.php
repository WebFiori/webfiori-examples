<?php
namespace App\Ini\Routes;

use App\Pages\DashboardPage;
use App\Pages\LoginPage;
use App\Pages\ProjectDetailPage;
use App\Pages\ProjectsPage;
use App\Pages\ReportsPage;
use App\Pages\SwaggerPage;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

class PagesRoutes {
    public static function create() {
        Router::page([
            RouteOption::PATH => '/login',
            RouteOption::TO => LoginPage::class,
            RouteOption::MIDDLEWARE => ['start-session'],
        ]);

        Router::page([
            RouteOption::PATH => '/api-docs',
            RouteOption::TO => SwaggerPage::class,
        ]);

        Router::page([
            RouteOption::PATH => '/dashboard',
            RouteOption::TO => DashboardPage::class,
            RouteOption::MIDDLEWARE => ['start-session', 'auth', 'refresh-profile'],
        ]);

        Router::page([
            RouteOption::PATH => '/projects',
            RouteOption::TO => ProjectsPage::class,
            RouteOption::MIDDLEWARE => ['start-session', 'auth', 'refresh-profile'],
        ]);

        Router::page([
            RouteOption::PATH => '/projects/{id}',
            RouteOption::TO => ProjectDetailPage::class,
            RouteOption::MIDDLEWARE => ['start-session', 'auth', 'refresh-profile'],
        ]);

        Router::page([
            RouteOption::PATH => '/reports',
            RouteOption::TO => ReportsPage::class,
            RouteOption::MIDDLEWARE => ['start-session', 'auth', 'refresh-profile'],
        ]);

        Router::page([
            RouteOption::PATH => '/admin/users',
            RouteOption::TO => \App\Pages\Admin\UsersPage::class,
            RouteOption::MIDDLEWARE => ['start-session', 'auth', 'refresh-profile'],
        ]);

        Router::page([
            RouteOption::PATH => '/admin/audit-log',
            RouteOption::TO => \App\Pages\Admin\AuditLogPage::class,
            RouteOption::MIDDLEWARE => ['start-session', 'auth', 'refresh-profile'],
        ]);

        Router::page([
            RouteOption::PATH => '/admin/settings',
            RouteOption::TO => \App\Pages\Admin\SettingsPage::class,
            RouteOption::MIDDLEWARE => ['start-session', 'auth', 'refresh-profile'],
        ]);
    }
}
