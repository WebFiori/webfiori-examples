<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Integration test that verifies the RateLimitMiddleware is active.
 *
 * Starts a real PHP built-in server so requests go through the full
 * router + middleware pipeline. Sends requests via HTTP and inspects
 * response headers to confirm rate limiting is applied.
 */
class RateLimitTest extends TestCase {
    private static $serverProcess;
    private static int $port = 8999;
    private static string $baseUrl;

    public static function setUpBeforeClass(): void {
        $root = dirname(__DIR__);

        // Flush rate limit cache to ensure a clean window
        $cacheDir = $root . '/vendor/webfiori/cache/WebFiori/Cache/cache';

        if (is_dir($cacheDir)) {
            array_map('unlink', glob($cacheDir . '/*'));
        }

        $cmd = sprintf(
            'php -S 127.0.0.1:%d -t %s/public > /dev/null 2>&1 & echo $!',
            self::$port,
            $root
        );

        $pid = trim(shell_exec($cmd));
        self::$serverProcess = (int) $pid;
        self::$baseUrl = 'http://127.0.0.1:' . self::$port;

        // Wait for server to be ready
        $attempts = 0;

        while ($attempts < 20) {
            $conn = @fsockopen('127.0.0.1', self::$port, $errno, $errstr, 0.1);

            if ($conn) {
                fclose($conn);
                break;
            }

            usleep(100000);
            $attempts++;
        }
    }

    public static function tearDownAfterClass(): void {
        if (self::$serverProcess > 0) {
            posix_kill(self::$serverProcess, SIGTERM);
        }
    }

    /**
     * Verifies that rate limit headers are present in the response.
     */
    public function testRateLimitHeadersPresent() {
        $headers = $this->getResponseHeaders('/apis/tasks');

        $this->assertArrayHasKey('x-ratelimit-limit', $headers);
        $this->assertArrayHasKey('x-ratelimit-remaining', $headers);
        $this->assertArrayHasKey('x-ratelimit-reset', $headers);
        $this->assertEquals('60', $headers['x-ratelimit-limit']);
    }

    /**
     * Verifies that the remaining count decreases with each request.
     */
    public function testRateLimitRemainingDecreases() {
        $headers1 = $this->getResponseHeaders('/apis/tasks');
        $remaining1 = (int) $headers1['x-ratelimit-remaining'];

        $headers2 = $this->getResponseHeaders('/apis/tasks');
        $remaining2 = (int) $headers2['x-ratelimit-remaining'];

        $this->assertLessThan($remaining1, $remaining2);
    }

    /**
     * Verifies that exceeding the limit returns 429 Too Many Requests.
     */
    public function testRateLimitReturns429WhenExceeded() {
        // First, check current remaining
        $headers = $this->getResponseHeaders('/apis/tasks');
        $remaining = (int) $headers['x-ratelimit-remaining'];

        // Exhaust remaining requests
        for ($i = 0; $i <= $remaining; $i++) {
            $this->getResponseHeaders('/apis/tasks');
        }

        // Next request should be 429
        $response = $this->request('/apis/tasks');
        $this->assertEquals(429, $response['code']);
    }

    /**
     * Sends a GET request and returns parsed response headers.
     *
     * @return array<string, string>
     */
    private function getResponseHeaders(string $path): array {
        $response = $this->request($path);

        return $response['headers'];
    }

    /**
     * Sends a GET request and returns code + headers + body.
     *
     * @return array{code: int, headers: array<string, string>, body: string}
     */
    private function request(string $path): array {
        $ch = curl_init(self::$baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_TIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $headerStr = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        $headers = [];

        foreach (explode("\r\n", $headerStr) as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }

        return ['code' => $code, 'headers' => $headers, 'body' => $body];
    }
}
