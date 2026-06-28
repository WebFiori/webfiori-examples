<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use WebFiori\Log\FileLogger;
use WebFiori\Log\LoggerFacade;
use WebFiori\Log\LogLevel;

class LoggingTest extends TestCase {
    private string $logDir;

    protected function setUp(): void {
        $this->logDir = dirname(__DIR__) . '/App/Storage/Logs';

        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }

        // Clear today's log file
        $logFile = $this->logDir . '/app-' . date('Y-m-d') . '.log';
        if (file_exists($logFile)) {
            unlink($logFile);
        }
    }

    public function testFileLoggerWritesToDailyFile(): void {
        $logger = new FileLogger($this->logDir);
        $logger->info('Test message', ['key' => 'value']);

        $logFile = $this->logDir . '/app-' . date('Y-m-d') . '.log';
        $this->assertFileExists($logFile);

        $content = file_get_contents($logFile);
        $this->assertStringContainsString('[INFO] Test message', $content);
        $this->assertStringContainsString('"key":"value"', $content);
    }

    public function testLevelFilteringSuppressesLowerLevels(): void {
        $logger = new FileLogger($this->logDir, LogLevel::WARNING);

        $logger->debug('Should not appear');
        $logger->info('Should not appear either');
        $logger->warning('Should appear');

        $logFile = $this->logDir . '/app-' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringNotContainsString('Should not appear', $content);
        $this->assertStringContainsString('[WARNING] Should appear', $content);
    }

    public function testAllLogLevels(): void {
        $logger = new FileLogger($this->logDir, LogLevel::DEBUG);

        $logger->debug('debug msg');
        $logger->info('info msg');
        $logger->warning('warning msg');
        $logger->error('error msg');
        $logger->critical('critical msg');

        $logFile = $this->logDir . '/app-' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString('[DEBUG] debug msg', $content);
        $this->assertStringContainsString('[INFO] info msg', $content);
        $this->assertStringContainsString('[WARNING] warning msg', $content);
        $this->assertStringContainsString('[ERROR] error msg', $content);
        $this->assertStringContainsString('[CRITICAL] critical msg', $content);
    }

    public function testLoggerFacade(): void {
        LoggerFacade::setInstance(new FileLogger($this->logDir, LogLevel::DEBUG));
        LoggerFacade::info('Facade test', ['source' => 'test']);

        $logFile = $this->logDir . '/app-' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString('[INFO] Facade test', $content);
        $this->assertStringContainsString('"source":"test"', $content);

        LoggerFacade::reset();
    }

    public function testContextIsJsonEncoded(): void {
        $logger = new FileLogger($this->logDir);
        $logger->error('Payment failed', ['order_id' => 42, 'error' => 'Card declined']);

        $logFile = $this->logDir . '/app-' . date('Y-m-d') . '.log';
        $content = file_get_contents($logFile);

        $this->assertStringContainsString('{"order_id":42,"error":"Card declined"}', $content);
    }
}
