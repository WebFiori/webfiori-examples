<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Verifies middleware dependency resolution.
 *
 * The composer test script starts a server, hits /test-chain, and stops.
 * This test then reads the log file to verify all 5 middleware executed
 * in the correct dependency order.
 *
 * Route has middleware assigned in REVERSE order: ['mw-e', 'mw-d', 'mw-c', 'mw-b', 'mw-a']
 * Framework's topological sort reorders them based on getDependencies():
 *   before:    A → B → C → D → E
 *   after:     E → D → C → B → A
 *   afterSend: E → D → C → B → A
 */
class MiddlewareChainTest extends TestCase {
    private static string $logDir;

    public static function setUpBeforeClass(): void {
        self::$logDir = dirname(__DIR__) . '/App/Storage/Logs';
    }

    public function testLogFileExists() {
        $logFiles = glob(self::$logDir . '/app-*.log');
        $this->assertNotEmpty($logFiles, 'Log file should exist (run: composer test)');
    }

    /**
     * @depends testLogFileExists
     */
    public function testAllBeforeMethodsExecuted() {
        $log = $this->getLog();
        $this->assertStringContainsString('A::before', $log);
        $this->assertStringContainsString('B::before', $log);
        $this->assertStringContainsString('C::before', $log);
        $this->assertStringContainsString('D::before', $log);
        $this->assertStringContainsString('E::before', $log);
    }

    /**
     * @depends testLogFileExists
     */
    public function testRouteHandlerExecuted() {
        $this->assertStringContainsString('Route::handler', $this->getLog());
    }

    /**
     * @depends testLogFileExists
     */
    public function testAllAfterMethodsExecuted() {
        $log = $this->getLog();
        $this->assertStringContainsString('A::after', $log);
        $this->assertStringContainsString('B::after', $log);
        $this->assertStringContainsString('C::after', $log);
        $this->assertStringContainsString('D::after', $log);
        $this->assertStringContainsString('E::after', $log);
    }

    /**
     * @depends testLogFileExists
     */
    public function testAllAfterSendMethodsExecuted() {
        $log = $this->getLog();
        $this->assertStringContainsString('A::afterSend', $log);
        $this->assertStringContainsString('B::afterSend', $log);
        $this->assertStringContainsString('C::afterSend', $log);
        $this->assertStringContainsString('D::afterSend', $log);
        $this->assertStringContainsString('E::afterSend', $log);
    }

    /**
     * @depends testLogFileExists
     */
    public function testBeforeOrderIsDependencyOrder() {
        $log = $this->getLog();

        $posA = strpos($log, 'A::before');
        $posB = strpos($log, 'B::before');
        $posC = strpos($log, 'C::before');
        $posD = strpos($log, 'D::before');
        $posE = strpos($log, 'E::before');

        $this->assertLessThan($posB, $posA, 'A::before should precede B::before');
        $this->assertLessThan($posC, $posB, 'B::before should precede C::before');
        $this->assertLessThan($posD, $posC, 'C::before should precede D::before');
        $this->assertLessThan($posE, $posD, 'D::before should precede E::before');
    }

    /**
     * @depends testLogFileExists
     */
    public function testAfterOrderIsReversed() {
        $log = $this->getLog();

        $posE = strpos($log, 'E::after');
        $posD = strpos($log, 'D::after');
        $posC = strpos($log, 'C::after');
        $posB = strpos($log, 'B::after');
        $posA = strpos($log, 'A::after');

        $this->assertLessThan($posD, $posE, 'E::after should precede D::after');
        $this->assertLessThan($posC, $posD, 'D::after should precede C::after');
        $this->assertLessThan($posB, $posC, 'C::after should precede B::after');
        $this->assertLessThan($posA, $posB, 'B::after should precede A::after');
    }

    /**
     * @depends testLogFileExists
     */
    public function testHandlerBetweenBeforeAndAfter() {
        $log = $this->getLog();

        $lastBefore = strpos($log, 'E::before');
        $handler = strpos($log, 'Route::handler');
        $firstAfter = strpos($log, 'E::after');

        $this->assertLessThan($handler, $lastBefore);
        $this->assertLessThan($firstAfter, $handler);
    }

    private function getLog(): string {
        $files = glob(self::$logDir . '/app-*.log');

        return !empty($files) ? file_get_contents($files[0]) : '';
    }
}
