<?php
namespace Tests;

use App\Domain\Product;
use App\Domain\ProductCatalog;
use App\Domain\ProductRepositoryInterface;
use App\Infrastructure\ArrayStorage;
use App\Infrastructure\InMemoryProductRepository;
use PHPUnit\Framework\TestCase;
use WebFiori\Cache\Cache;
use WebFiori\Cache\CacheFacade;
use WebFiori\Container\ContainerFacade;

class CachingTest extends TestCase {

    private InMemoryProductRepository $repo;
    private Cache $cache;
    private ProductCatalog $catalog;

    protected function setUp(): void {
        ContainerFacade::reset();
        $this->repo  = new InMemoryProductRepository();
        $this->cache = new Cache(new ArrayStorage());
        $this->catalog = new ProductCatalog($this->repo, $this->cache);
    }

    // --- Cache-aside pattern ---

    public function testCacheMissHitsRepository(): void {
        $products = $this->catalog->getByCategory('electronics');

        $this->assertCount(3, $products);
        $this->assertEquals(1, $this->repo->getQueryCount());
    }

    public function testCacheHitSkipsRepository(): void {
        $this->catalog->getByCategory('electronics'); // miss — queries repo
        $this->repo->resetQueryCount();

        $this->catalog->getByCategory('electronics'); // hit — repo not called
        $this->assertEquals(0, $this->repo->getQueryCount());
    }

    public function testDifferentCategoriesStoredSeparately(): void {
        $electronics = $this->catalog->getByCategory('electronics');
        $furniture   = $this->catalog->getByCategory('furniture');

        $this->assertCount(3, $electronics);
        $this->assertCount(2, $furniture);
        $this->assertEquals(2, $this->repo->getQueryCount());
    }

    public function testGetByIdCachesIndividualProduct(): void {
        $product = $this->catalog->getById(1);
        $this->repo->resetQueryCount();

        $cached = $this->catalog->getById(1);
        $this->assertEquals(0, $this->repo->getQueryCount());
        $this->assertEquals($product->id, $cached->id);
        $this->assertEquals($product->name, $cached->name);
    }

    public function testGetByIdReturnsNullForUnknown(): void {
        $result = $this->catalog->getById(999);
        $this->assertNull($result);
    }

    // --- Prefix isolation ---

    public function testPrefixIsolationKeepsNamespacesSeparate(): void {
        $electronics = $this->cache->withPrefix('products:');
        $users       = $this->cache->withPrefix('users:');

        $electronics->set('count', 3, 60);
        $users->set('count', 42, 60);

        $this->assertEquals(3, $electronics->get('count'));
        $this->assertEquals(42, $users->get('count'));
    }

    public function testWithPrefixDoesNotMutateOriginal(): void {
        $prefixed = $this->cache->withPrefix('test:');
        $prefixed->set('key', 'value', 60);

        // Original cache (no prefix) cannot find the key without the prefix
        $this->assertNull($this->cache->get('key'));
        // Original cache prefix is empty string — different from 'test:'
        $this->assertEquals('', $this->cache->getPrefix());
        $this->assertEquals('test:', $prefixed->getPrefix());
    }

    public function testFlushRespectsPrefix(): void {
        $products = $this->cache->withPrefix('products:');
        $users    = $this->cache->withPrefix('users:');

        $products->set('laptops', ['a', 'b'], 60);
        $users->set('session', 'xyz', 60);

        // Flush only products namespace
        $products->flush();

        $this->assertNull($products->get('laptops'));
        $this->assertEquals('xyz', $users->get('session')); // unaffected
    }

    // --- Invalidation ---

    public function testInvalidateClearsAllProductEntries(): void {
        $this->catalog->getByCategory('electronics');
        $this->catalog->getById(1);
        $this->repo->resetQueryCount();

        $this->catalog->invalidate();

        // After invalidation, both queries hit the repo again
        $this->catalog->getByCategory('electronics');
        $this->catalog->getById(1);
        $this->assertEquals(2, $this->repo->getQueryCount());
    }

    // --- TTL and expiry ---

    public function testExpiredItemTreatedAsCacheMiss(): void {
        $storage = new ArrayStorage();
        $cache   = new Cache($storage);

        // Store with TTL of 1 second
        $cache->set('short-lived', 'hello', 1);
        $this->assertTrue($cache->has('short-lived'));

        // Simulate expiry by waiting — use purgeExpired via sleep
        sleep(2);
        $this->assertFalse($cache->has('short-lived'));
    }

    public function testPurgeExpiredRemovesStaleEntries(): void {
        $storage = new ArrayStorage();
        $cache   = new Cache($storage);

        $cache->set('stale', 'data', 1);
        $cache->set('fresh', 'data', 300);

        sleep(2);
        $removed = $cache->purgeExpired();

        $this->assertEquals(1, $removed);
        $this->assertFalse($cache->has('stale'));
        $this->assertTrue($cache->has('fresh'));
    }

    // --- Custom storage driver ---

    public function testCustomArrayStorageDriver(): void {
        $storage = new ArrayStorage();
        $cache   = new Cache($storage);

        $cache->set('key', 'value', 60);
        $this->assertEquals('value', $cache->get('key'));
        $this->assertEquals(1, $storage->count());

        $cache->delete('key');
        $this->assertEquals(0, $storage->count());
    }

    // --- CacheFacade ---

    public function testCacheFacadeWithCustomInstance(): void {
        $cache = new Cache(new ArrayStorage());
        CacheFacade::setInstance($cache);

        CacheFacade::set('facade-key', 'facade-value', 60);
        $this->assertEquals('facade-value', CacheFacade::get('facade-key'));

        CacheFacade::reset();
    }

    public function testCacheFacadeWithPrefix(): void {
        $cache = new Cache(new ArrayStorage());
        CacheFacade::setInstance($cache);

        $prefixed = CacheFacade::withPrefix('test:');
        $prefixed->set('item', 42, 60);

        $this->assertEquals(42, $prefixed->get('item'));
        $this->assertNull(CacheFacade::get('item')); // not in root namespace

        CacheFacade::reset();
    }
}
