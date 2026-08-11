<?php
namespace App\Ini;

use App\Domain\ProductCatalog;
use App\Domain\ProductRepositoryInterface;
use App\Infrastructure\ArrayStorage;
use App\Infrastructure\InMemoryProductRepository;
use WebFiori\Cache\Cache;
use WebFiori\Cache\FileStorage;
use WebFiori\Container\ContainerFacade;

class Privileges {
    public static function initialize(): void {
        if (getenv('APP_ENV') === 'testing') {
            // In tests: use fast in-memory storage, no disk I/O
            ContainerFacade::singleton(Cache::class, function () {
                return new Cache(new ArrayStorage());
            });
        } else {
            // In production: use file-based storage
            ContainerFacade::singleton(Cache::class, function () {
                return new Cache(new FileStorage(APP_PATH.'App'.DIRECTORY_SEPARATOR.'Storage'.DIRECTORY_SEPARATOR.'Cache'));
            });
        }

        ContainerFacade::singleton(ProductRepositoryInterface::class, InMemoryProductRepository::class);

        // ProductCatalog needs Cache injected — use a factory
        ContainerFacade::bind(ProductCatalog::class, function ($container) {
            return new ProductCatalog(
                $container->make(ProductRepositoryInterface::class),
                $container->make(Cache::class)
            );
        });
    }
}
