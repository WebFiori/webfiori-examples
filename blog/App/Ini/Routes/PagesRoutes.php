<?php
namespace App\Ini\Routes;

use App\Pages\CategoryPostsView;
use App\Pages\HomePageView;
use App\Pages\LoginPage;
use App\Pages\PostDetailView;
use WebFiori\Framework\Middleware\StartSessionMiddleware;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

/**
 * Registers all page routes for the blog.
 *
 * All routes use the `start-session` middleware so session cookies
 * are properly set. Admin routes additionally use the `auth` middleware.
 */
class PagesRoutes {
    public static function create() {
        // Public pages
        Router::page([
            RouteOption::PATH => '/',
            RouteOption::TO => HomePageView::class,
            RouteOption::MIDDLEWARE => [StartSessionMiddleware::class]
        ]);
        Router::page([
            RouteOption::PATH => '/posts/{slug}',
            RouteOption::TO => PostDetailView::class,
            RouteOption::MIDDLEWARE => [StartSessionMiddleware::class]
        ]);
        Router::page([
            RouteOption::PATH => '/categories/{slug}',
            RouteOption::TO => CategoryPostsView::class,
            RouteOption::MIDDLEWARE => [StartSessionMiddleware::class]
        ]);
        Router::page([
            RouteOption::PATH => '/login',
            RouteOption::TO => LoginPage::class,
            RouteOption::MIDDLEWARE => [StartSessionMiddleware::class]
        ]);

        // Admin pages (protected by auth middleware)
        Router::page([
            RouteOption::PATH => '/admin',
            RouteOption::MIDDLEWARE => [StartSessionMiddleware::class],
            RouteOption::SUB_ROUTES => [
                [
                    RouteOption::PATH => '/',
                    RouteOption::TO => \App\Pages\Admin\DashboardPage::class,
                ],
                [
                    RouteOption::PATH => '/posts/create',
                    RouteOption::TO => \App\Pages\Admin\PostEditorPage::class,
                ],
                [
                    RouteOption::PATH => '/posts/{id}/edit',
                    RouteOption::TO => \App\Pages\Admin\PostEditorPage::class,
                ],
                [
                    RouteOption::PATH => '/categories',
                    RouteOption::TO => \App\Pages\Admin\CategoriesPage::class,
                ],
            ]
        ]);
    }
}
