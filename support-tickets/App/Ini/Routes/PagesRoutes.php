<?php
namespace App\Ini\Routes;

use App\Pages\SubmitTicketPage;
use App\Pages\TicketDetailPage;
use App\Pages\TicketListPage;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;

class PagesRoutes {
    public static function create() {
        Router::page([
            RouteOption::PATH => '/submit',
            RouteOption::TO => SubmitTicketPage::class,
        ]);
        Router::page([
            RouteOption::PATH => '/tickets',
            RouteOption::TO => TicketListPage::class,
        ]);
        Router::page([
            RouteOption::PATH => '/tickets/{id}',
            RouteOption::TO => TicketDetailPage::class,
        ]);
    }
}
