<?php
namespace Tests;

use App\Middleware\AppSessionMiddleware;
use App\Session\ArraySessionStorage;
use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Session\SessionManager;
use WebFiori\Framework\Session\SessionsManager;

/**
 * Tests for session functionality using the SessionsManager facade.
 * 
 * These tests demonstrate:
 * - Custom ArraySessionStorage works correctly
 * - SessionsManager facade for get/set/remove/pull
 * - AppSessionMiddleware configuration
 */
class SessionMiddlewareTest extends TestCase {

    private ArraySessionStorage $storage;
    private SessionManager $manager;

    protected function setUp(): void {
        // Create our own manager with array storage FIRST, before any getInstance call
        $this->storage = new ArraySessionStorage();
        $this->manager = new SessionManager($this->storage);
        
        // Inject it into the facade to bypass DefaultSessionStorage
        SessionsManager::setManager($this->manager);
    }

    protected function tearDown(): void {
        // Destroy active session if any, then reset the singleton
        if (SessionsManager::getActiveSession() !== null) {
            SessionsManager::destroy();
        }
        SessionsManager::reset();
    }

    // --- SessionsManager facade ---

    public function testStartSession(): void {
        SessionsManager::start('test-session');
        
        $active = SessionsManager::getActiveSession();
        $this->assertNotNull($active);
        $this->assertEquals('test-session', $active->getName());
        $this->assertTrue($active->isRunning());
    }

    public function testSetAndGetVariable(): void {
        SessionsManager::start('test-session');
        
        SessionsManager::set('user_id', 42);
        $this->assertEquals(42, SessionsManager::get('user_id'));
    }

    public function testPullRemovesAfterRead(): void {
        SessionsManager::start('test-session');
        
        SessionsManager::set('flash', 'success message');
        $value = SessionsManager::pull('flash');

        $this->assertEquals('success message', $value);
        $this->assertNull(SessionsManager::get('flash'));
    }

    public function testRemoveVariable(): void {
        SessionsManager::start('test-session');
        
        SessionsManager::set('temp', 'value');
        $this->assertTrue(SessionsManager::remove('temp'));
        $this->assertNull(SessionsManager::get('temp'));
    }

    // --- Session ID ---

    public function testNewIdChangesId(): void {
        SessionsManager::start('test-session');
        $oldId = SessionsManager::getActiveSession()->getId();

        $newId = SessionsManager::newId();

        $this->assertNotEquals($oldId, $newId);
        $this->assertEquals($newId, SessionsManager::getActiveSession()->getId());
    }

    public function testNewIdPreservesVariables(): void {
        SessionsManager::start('test-session');
        SessionsManager::set('user_id', 99);

        SessionsManager::newId();

        $this->assertEquals(99, SessionsManager::get('user_id'));
    }

    // --- Session lifecycle ---

    public function testSessionPauseAndResume(): void {
        SessionsManager::start('test-session');
        SessionsManager::set('data', 'preserved');
        
        SessionsManager::pauseAll();
        $this->assertNull(SessionsManager::getActiveSession());
        
        SessionsManager::start('test-session'); // Resumes existing session
        $this->assertEquals('preserved', SessionsManager::get('data'));
    }

    // --- Custom middleware configuration ---

    public function testAppSessionMiddlewareConfiguration(): void {
        $middleware = new AppSessionMiddleware();

        $this->assertEquals('myapp', $middleware->getSessionName());
        
        $options = $middleware->getSessionOptions();
        $this->assertEquals(30, $options['duration']);
        $this->assertTrue($options['refresh']);
    }

    // --- Custom storage ---

    public function testArrayStorageSavesAndReads(): void {
        $storage = new ArraySessionStorage();
        
        $storage->save('test-id-123', 'serialized-data');
        
        $this->assertTrue($storage->has('test-id-123'));
        $this->assertEquals('serialized-data', $storage->read('test-id-123'));
        $this->assertEquals(1, $storage->count());
    }

    public function testArrayStorageRemove(): void {
        $storage = new ArraySessionStorage();
        
        $storage->save('test-id', 'data');
        $this->assertTrue($storage->has('test-id'));
        
        $storage->remove('test-id');
        $this->assertFalse($storage->has('test-id'));
    }

    public function testGcRemovesOldSessions(): void {
        $storage = new ArraySessionStorage();
        
        $storage->save('session-1', 'data-1');
        $storage->save('session-2', 'data-2');
        $this->assertEquals(2, $storage->count());

        // GC with a future threshold removes all sessions
        $future = date('Y-m-d H:i:s', time() + 3600);
        $storage->gc($future);

        $this->assertEquals(0, $storage->count());
    }
}
