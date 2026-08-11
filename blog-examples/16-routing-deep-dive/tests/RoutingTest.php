<?php
namespace Tests;

use App\Apis\Admin\UsersService;
use App\Apis\ProductsService;
use App\Apis\UserProfileService;
use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Router\ServiceRouter;
use WebFiori\Framework\Router\Router;

class RoutingTest extends TestCase {

    protected function setUp(): void {
        ServiceRouter::reset();
        Router::removeAll();
    }

    // --- ServiceRouter URL derivation ---

    public function testDiscoverRegistersProductsRoute(): void {
        $count = ServiceRouter::discover('App\\Apis', '/apis');

        $discovered = ServiceRouter::getDiscovered();
        $this->assertArrayHasKey('products', $discovered);
        $this->assertEquals('/apis/products', $discovered['products']['path']);
    }

    public function testDiscoverRegistersUserProfileRoute(): void {
        ServiceRouter::discover('App\\Apis', '/apis');

        $discovered = ServiceRouter::getDiscovered();
        $this->assertArrayHasKey('user-profile', $discovered);
        $this->assertEquals('/apis/user-profile', $discovered['user-profile']['path']);
    }

    public function testDiscoverCountsRegisteredRoutes(): void {
        $count = ServiceRouter::discover('App\\Apis', '/apis');

        // ProductsService + UserProfileService = 2 (not Admin subdir — not recursive)
        $this->assertEquals(2, $count);
    }

    // --- Recursive discovery: subdirectories become path segments ---

    public function testRecursiveDiscoverIncludesSubdirectory(): void {
        ServiceRouter::discover('App\\Apis', '/apis', [], null, true);

        $discovered = ServiceRouter::getDiscovered();
        $this->assertArrayHasKey('admin/users', $discovered);
        $this->assertEquals('/apis/admin/users', $discovered['admin/users']['path']);
    }

    public function testRecursiveDiscoverCountsAllServices(): void {
        $count = ServiceRouter::discover('App\\Apis', '/apis', [], null, true);

        // ProductsService + UserProfileService + Admin/UsersService = 3
        $this->assertEquals(3, $count);
    }

    // --- RestController attribute path ---

    public function testRestControllerAttributePathIsUsed(): void {
        ServiceRouter::discover('App\\Apis', '/apis');

        $discovered = ServiceRouter::getDiscovered();
        // ProductsService has #[RestController('products', ...)]
        // The path attribute 'products' is used, not derived from class name
        $this->assertEquals('/apis/products', $discovered['products']['path']);
        $this->assertEquals(ProductsService::class, $discovered['products']['class']);
    }

    // --- Class name derivation rules ---

    public function testClassNameDerivationStripsServiceSuffix(): void {
        // UserProfileService -> UserProfile -> user-profile
        ServiceRouter::discover('App\\Apis', '/apis');

        $discovered = ServiceRouter::getDiscovered();
        $this->assertArrayHasKey('user-profile', $discovered);
    }

    // --- Service type ---

    public function testDiscoveredServicesAreOfTypeService(): void {
        ServiceRouter::discover('App\\Apis', '/apis');

        $discovered = ServiceRouter::getDiscovered();
        $this->assertEquals('service', $discovered['products']['type']);
        $this->assertEquals('service', $discovered['user-profile']['type']);
    }

    // --- Router routes registered ---

    public function testDiscoverRegistersRoutesInRouter(): void {
        ServiceRouter::discover('App\\Apis', '/apis');

        $routes = Router::routes();
        $uris   = array_keys($routes);

        $hasProducts    = count(array_filter($uris, fn($u) => str_ends_with($u, '/apis/products'))) > 0;
        $hasUserProfile = count(array_filter($uris, fn($u) => str_ends_with($u, '/apis/user-profile'))) > 0;

        $this->assertTrue($hasProducts);
        $this->assertTrue($hasUserProfile);
    }

    // --- Base path variations ---

    public function testDiscoverWithDifferentBasePath(): void {
        ServiceRouter::discover('App\\Apis', '/v2/api');

        $discovered = ServiceRouter::getDiscovered();
        $this->assertEquals('/v2/api/products', $discovered['products']['path']);
    }
}
