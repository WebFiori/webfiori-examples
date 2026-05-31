<?php
namespace Tests;

use App\Apis\BlogServicesManager;
use App\Tasks\WeeklyDigestTask;
use WebFiori\Cache\CacheFacade;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\APITestCase;

/**
 * Tests for the new enterprise features: caching, email notifications, and weekly digest.
 */
class EnterpriseFeaturesTest extends APITestCase {
    /**
     * Verifies that listing posts uses cache on second call.
     */
    public function testPostListingIsCached() {
        CacheFacade::flush();

        // First call populates cache
        $output1 = $this->getRequest($this->mgr(), 'posts', ['page' => 1, 'perPage' => 5]);
        $response1 = json_decode($output1, true);
        $this->assertArrayHasKey('data', $response1);

        // Verify cache key exists
        $this->assertTrue(CacheFacade::has('posts:list:p1:pp5:call'));
    }

    /**
     * Verifies that creating a post invalidates the cache.
     */
    public function testCacheInvalidatedOnCreate() {
        CacheFacade::flush();

        // Populate cache
        $this->getRequest($this->mgr(), 'posts', ['page' => 1, 'perPage' => 5]);
        $this->assertTrue(CacheFacade::has('posts:list:p1:pp5:call'));

        // Create a post (need auth)
        $this->authenticate();
        $output = $this->postRequest($this->mgr(), 'posts', [
            'title' => 'Cache Test Post',
            'slug' => 'cache-test-' . time(),
            'content' => 'Testing cache invalidation',
            'status' => 'draft'
        ]);
        $response = json_decode($output, true);

        // Verify post was actually created (not auth error)
        $this->assertArrayHasKey('data', $response, 'Post creation failed: ' . $output);

        // Cache should be cleared
        $this->assertFalse(CacheFacade::has('posts:list:p1:pp5:call'));
    }

    /**
     * Verifies that adding a comment stores a notification email.
     */
    public function testCommentNotificationEmailStored() {
        $emailDir = APP_PATH . 'Storage' . DS . 'Logs' . DS . 'emails';

        // Count existing emails (stored in subdirectories by subject)
        $before = is_dir($emailDir) ? count(glob($emailDir . '/*/*.html')) : 0;

        $this->postRequest($this->mgr(), 'comments', [
            'postId' => 1,
            'authorName' => 'Email Tester',
            'authorEmail' => 'emailtest@example.com',
            'content' => 'Testing email notification'
        ]);

        $after = count(glob($emailDir . '/*/*.html'));
        $this->assertGreaterThan($before, $after, 'A notification email should be stored.');
    }

    /**
     * Verifies that the weekly digest task executes without error.
     */
    public function testWeeklyDigestTaskExecutes() {
        $task = new WeeklyDigestTask();
        // Execute should not throw
        $task->execute();
        $this->assertTrue(true);
    }

    private function authenticate(): void {
        SessionsManager::start('wf-session');
        SessionsManager::set('author-id', 1);
        SessionsManager::set('author-name', 'Admin');
    }

    private function mgr(): BlogServicesManager {
        return new BlogServicesManager();
    }
}
