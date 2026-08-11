<?php
namespace Tests;

use App\Session\ArraySessionStorage;
use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Session\Session;
use WebFiori\Framework\Session\SessionOption;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\Session\SessionStatus;

class SessionsTest extends TestCase {

    private ArraySessionStorage $storage;

    protected function setUp(): void {
        $this->storage = new ArraySessionStorage();
        SessionsManager::reset();
        SessionsManager::setStorage($this->storage);
    }

    // --- Basic operations ---

    public function testStartNewSession(): void {
        SessionsManager::start('app');

        $session = SessionsManager::getActiveSession();
        $this->assertNotNull($session);
        $this->assertEquals('app', $session->getName());
        $this->assertEquals(SessionStatus::NEW, $session->getStatus());
        $this->assertTrue($session->isRunning());
    }

    public function testSetAndGetVariable(): void {
        SessionsManager::start('app');

        SessionsManager::set('user_id', 42);
        $this->assertEquals(42, SessionsManager::get('user_id'));
    }

    public function testRemoveVariable(): void {
        SessionsManager::start('app');

        SessionsManager::set('temp', 'value');
        $this->assertTrue(SessionsManager::remove('temp'));
        $this->assertNull(SessionsManager::get('temp'));
    }

    public function testPullRemovesAfterRead(): void {
        SessionsManager::start('app');

        SessionsManager::set('flash', 'success message');
        $value = SessionsManager::pull('flash');

        $this->assertEquals('success message', $value);
        $this->assertNull(SessionsManager::get('flash'));
    }

    public function testSessionPersistsAfterClose(): void {
        SessionsManager::start('app');
        $sessionId = SessionsManager::getActiveSession()->getId();

        SessionsManager::set('key', 'persisted-value');
        SessionsManager::close();

        // Session data should be in storage
        $this->assertTrue($this->storage->has($sessionId));
    }

    public function testSessionResumesWithSameId(): void {
        SessionsManager::start('app');
        $sessionId = SessionsManager::getActiveSession()->getId();
        SessionsManager::set('key', 'hello');
        SessionsManager::close();

        // Simulate next request: start with same ID
        SessionsManager::reset();
        SessionsManager::setStorage($this->storage);
        SessionsManager::start('app', [SessionOption::SESSION_ID => $sessionId]);

        $this->assertEquals(SessionStatus::RESUMED, SessionsManager::getActiveSession()->getStatus());
        $this->assertEquals('hello', SessionsManager::get('key'));
    }

    // --- Named sessions ---

    public function testNamedSessionsAreIsolated(): void {
        SessionsManager::start('auth');
        SessionsManager::set('user_id', 7);
        SessionsManager::close();

        SessionsManager::start('cart');
        SessionsManager::set('items', ['product-1', 'product-2']);
        SessionsManager::close();

        // Each named session has its own variables
        SessionsManager::start('auth');
        $this->assertEquals(7, SessionsManager::get('user_id'));
        $this->assertNull(SessionsManager::get('items')); // cart var not visible in auth session
        SessionsManager::close();

        SessionsManager::start('cart');
        $this->assertEquals(['product-1', 'product-2'], SessionsManager::get('items'));
        $this->assertNull(SessionsManager::get('user_id')); // auth var not visible in cart session
    }

    // --- Session duration ---

    public function testSessionDefaultDuration(): void {
        SessionsManager::start('app');

        $session = SessionsManager::getActiveSession();
        $this->assertEquals(7200, $session->getDuration()); // 120 minutes default
    }

    public function testSessionCustomDuration(): void {
        SessionsManager::start('app', [SessionOption::DURATION => 30]);

        $session = SessionsManager::getActiveSession();
        $this->assertEquals(1800, $session->getDuration()); // 30 minutes = 1800 seconds
    }

    public function testNonPersistentSession(): void {
        SessionsManager::start('temp', [SessionOption::DURATION => 0]);

        $session = SessionsManager::getActiveSession();
        $this->assertFalse($session->isPersistent());
        $this->assertEquals(0, $session->getDuration());
    }

    // --- Session ID regeneration ---

    public function testReGenerateIdChangesId(): void {
        SessionsManager::start('app');
        $oldId = SessionsManager::getActiveSession()->getId();

        $newId = SessionsManager::newId();

        $this->assertNotEquals($oldId, $newId);
        $this->assertEquals($newId, SessionsManager::getActiveSession()->getId());
    }

    public function testReGenerateIdPreservesVariables(): void {
        SessionsManager::start('app');
        SessionsManager::set('user_id', 99);

        SessionsManager::newId();

        $this->assertEquals(99, SessionsManager::get('user_id'));
    }

    // --- Destroy ---

    public function testDestroyKillsSession(): void {
        SessionsManager::start('app');
        $sessionId = SessionsManager::getActiveSession()->getId();

        SessionsManager::set('key', 'value');
        SessionsManager::destroy();

        // After destroy, storage entry is removed
        $this->assertFalse($this->storage->has($sessionId));
        $this->assertNull(SessionsManager::getActiveSession());
    }

    // --- Session encryption ---

    public function testSessionEncryptionWithKey(): void {
        if (!defined('SESSION_KEY')) {
            define('SESSION_KEY', 'test-encryption-key-for-sessions-32!');
        }

        SessionsManager::start('app');
        $sessionId = SessionsManager::getActiveSession()->getId();
        SessionsManager::set('secret', 'top-secret-value');
        SessionsManager::close();

        $raw = $this->storage->read($sessionId);

        // Encrypted sessions start with 'ENC:'
        $this->assertStringStartsWith('ENC:', $raw);

        // But the session can still be resumed and decrypted transparently
        SessionsManager::reset();
        SessionsManager::setStorage($this->storage);
        SessionsManager::start('app', [SessionOption::SESSION_ID => $sessionId]);

        $this->assertEquals('top-secret-value', SessionsManager::get('secret'));
    }

    // --- Custom storage ---

    public function testArrayStorageSavesAndReads(): void {
        SessionsManager::start('app');
        $sessionId = SessionsManager::getActiveSession()->getId();
        SessionsManager::set('value', 42);
        SessionsManager::close();

        $this->assertTrue($this->storage->has($sessionId));
        $this->assertNotNull($this->storage->read($sessionId));
        $this->assertEquals(1, $this->storage->count());
    }

    public function testGcRemovesOldSessions(): void {
        SessionsManager::start('app');
        $sessionId = SessionsManager::getActiveSession()->getId();
        SessionsManager::close();

        $this->assertEquals(1, $this->storage->count());

        // GC with a future threshold removes all sessions
        $future = date('Y-m-d H:i:s', time() + 3600);
        $this->storage->gc($future);

        $this->assertEquals(0, $this->storage->count());
    }

    public function testGcRespectsMaxCount(): void {
        // Create 3 sessions
        for ($i = 1; $i <= 3; $i++) {
            SessionsManager::reset();
            SessionsManager::setStorage($this->storage);
            SessionsManager::start("session-$i");
            SessionsManager::close();
        }

        $this->assertEquals(3, $this->storage->count());

        // GC with maxCount=2 removes at most 2
        $future = date('Y-m-d H:i:s', time() + 3600);
        $this->storage->gc($future, 2);

        $this->assertEquals(1, $this->storage->count());
    }
}
