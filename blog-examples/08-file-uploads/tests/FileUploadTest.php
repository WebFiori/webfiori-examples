<?php
namespace Tests;

use WebFiori\File\StreamingUploader;
use WebFiori\File\ResumableUploader;
use WebFiori\File\Exceptions\FileException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the streaming and resumable upload features.
 */
class FileUploadTest extends TestCase {
    private string $uploadDir;
    private string $tmpDir;

    protected function setUp(): void {
        $this->uploadDir = dirname(__DIR__) . '/App/Storage/Uploads';
        $this->tmpDir = sys_get_temp_dir() . '/wf-upload-test-' . uniqid();
        mkdir($this->tmpDir, 0755, true);

        // Ensure upload dir exists and is clean for tests
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    protected function tearDown(): void {
        // Clean up test files
        $this->cleanDir($this->tmpDir);
        // Clean uploaded files
        foreach (glob($this->uploadDir . '/test-*') as $f) {
            unlink($f);
        }
        $partialDir = $this->uploadDir . '/.partial';
        if (is_dir($partialDir)) {
            $this->cleanDir($partialDir);
        }
    }

    private function cleanDir(string $dir): void {
        if (!is_dir($dir)) {
            return;
        }
        foreach (glob($dir . '/*') as $f) {
            is_file($f) ? unlink($f) : $this->cleanDir($f);
        }
        @rmdir($dir);
    }

    // --- StreamingUploader Tests ---

    public function testStreamingUploadSuccess() {
        // Create a temp file to serve as php://input
        $inputFile = $this->tmpDir . '/input.txt';
        file_put_contents($inputFile, 'Hello, streaming upload!');

        $uploader = new StreamingUploader($this->uploadDir, ['txt'], $inputFile);
        $file = $uploader->receive('test-streaming.txt');

        $this->assertTrue($file->isUploaded());
        $this->assertEquals('test-streaming.txt', $file->getName());
        $this->assertFileExists($this->uploadDir . '/test-streaming.txt');
        $this->assertEquals('Hello, streaming upload!', file_get_contents($this->uploadDir . '/test-streaming.txt'));
    }

    public function testStreamingUploadRejectsInvalidExtension() {
        $inputFile = $this->tmpDir . '/input.exe';
        file_put_contents($inputFile, 'malicious content');

        $uploader = new StreamingUploader($this->uploadDir, ['txt', 'pdf'], $inputFile);

        $this->expectException(FileException::class);
        $uploader->receive('test-malware.exe');
    }

    public function testStreamingUploadRejectsOversizedFile() {
        $inputFile = $this->tmpDir . '/input-large.txt';
        file_put_contents($inputFile, str_repeat('x', 1024)); // 1KB

        $uploader = new StreamingUploader($this->uploadDir, ['txt'], $inputFile);
        $uploader->setMaxFileSize(512); // 512 bytes limit

        $this->expectException(FileException::class);
        $uploader->receive('test-large.txt');
    }

    public function testStreamingUploadFilenameFromHeader() {
        $inputFile = $this->tmpDir . '/input.txt';
        file_put_contents($inputFile, 'header filename test');

        $_SERVER['HTTP_X_FILENAME'] = 'test-from-header.txt';

        $uploader = new StreamingUploader($this->uploadDir, ['txt'], $inputFile);
        $file = $uploader->receive();

        $this->assertEquals('test-from-header.txt', $file->getName());
        $this->assertFileExists($this->uploadDir . '/test-from-header.txt');

        unset($_SERVER['HTTP_X_FILENAME']);
        @unlink($this->uploadDir . '/test-from-header.txt');
    }

    public function testStreamingUploadWithStreamProcessor() {
        $inputFile = $this->tmpDir . '/input.txt';
        file_put_contents($inputFile, 'processor test data');

        $checksum = null;
        $uploader = new StreamingUploader($this->uploadDir, ['txt'], $inputFile);
        $uploader->setStreamProcessor(function (\Generator $chunks, string $destPath) use (&$checksum) {
            $hash = hash_init('sha256');
            $dest = fopen($destPath, 'wb');

            foreach ($chunks as $chunk) {
                hash_update($hash, $chunk);
                fwrite($dest, $chunk);
            }

            fclose($dest);
            $checksum = hash_final($hash);
        });

        $file = $uploader->receive('test-processed.txt');

        $this->assertNotNull($checksum);
        $this->assertEquals(hash('sha256', 'processor test data'), $checksum);
        $this->assertFileExists($this->uploadDir . '/test-processed.txt');
    }

    public function testStreamingUploadBeforeCallbackRejects() {
        $inputFile = $this->tmpDir . '/input.txt';
        file_put_contents($inputFile, 'rejected content');

        $uploader = new StreamingUploader($this->uploadDir, ['txt'], $inputFile);
        $uploader->setOnBeforeUpload(function (array $fileInfo): bool {
            return false; // reject all uploads
        });

        $this->expectException(FileException::class);
        $uploader->receive('test-rejected.txt');
    }

    // --- ResumableUploader Tests ---

    public function testResumableUploadSingleChunk() {
        $inputFile = $this->tmpDir . '/chunk1.txt';
        file_put_contents($inputFile, 'single chunk content');

        $uploader = new ResumableUploader($this->uploadDir, ['txt'], $inputFile);
        $result = $uploader->receiveChunk('test-session-1', 'test-resumable.txt', true);

        $this->assertTrue($result['complete']);
        $this->assertNotNull($result['file']);
        $this->assertEquals('test-resumable.txt', $result['file']->getName());
        $this->assertFileExists($this->uploadDir . '/test-resumable.txt');
    }

    public function testResumableUploadMultipleChunks() {
        // Chunk 1
        $inputFile1 = $this->tmpDir . '/chunk1.txt';
        file_put_contents($inputFile1, 'AAAA');

        $uploader1 = new ResumableUploader($this->uploadDir, ['txt'], $inputFile1);
        $result1 = $uploader1->receiveChunk('test-session-2', 'test-multi-chunk.txt', false);

        $this->assertFalse($result1['complete']);
        $this->assertEquals(4, $result1['offset']);

        // Chunk 2 (final)
        $inputFile2 = $this->tmpDir . '/chunk2.txt';
        file_put_contents($inputFile2, 'BBBB');

        $uploader2 = new ResumableUploader($this->uploadDir, ['txt'], $inputFile2);
        $result2 = $uploader2->receiveChunk('test-session-2', 'test-multi-chunk.txt', true);

        $this->assertTrue($result2['complete']);
        $this->assertEquals(8, $result2['offset']);
        $this->assertFileExists($this->uploadDir . '/test-multi-chunk.txt');
        $this->assertEquals('AAAABBBB', file_get_contents($this->uploadDir . '/test-multi-chunk.txt'));
    }

    public function testResumableUploadGetOffset() {
        // Write a partial file
        $inputFile = $this->tmpDir . '/chunk.txt';
        file_put_contents($inputFile, 'partial data');

        $uploader = new ResumableUploader($this->uploadDir, ['txt'], $inputFile);
        $uploader->receiveChunk('test-session-3', 'test-offset.txt', false);

        // Check offset
        $offset = $uploader->getOffset('test-session-3', 'test-offset.txt');
        $this->assertEquals(12, $offset); // "partial data" = 12 bytes
    }

    public function testResumableUploadCancel() {
        $inputFile = $this->tmpDir . '/chunk.txt';
        file_put_contents($inputFile, 'to be cancelled');

        $uploader = new ResumableUploader($this->uploadDir, ['txt'], $inputFile);
        $uploader->receiveChunk('test-session-4', 'test-cancel.txt', false);

        // Verify partial exists
        $this->assertGreaterThan(0, $uploader->getOffset('test-session-4', 'test-cancel.txt'));

        // Cancel
        $uploader->cancel('test-session-4', 'test-cancel.txt');

        // Verify partial is gone
        $this->assertEquals(0, $uploader->getOffset('test-session-4', 'test-cancel.txt'));
    }

    public function testResumableUploadRejectsInvalidExtension() {
        $inputFile = $this->tmpDir . '/chunk.exe';
        file_put_contents($inputFile, 'bad content');

        $uploader = new ResumableUploader($this->uploadDir, ['txt', 'pdf'], $inputFile);

        $this->expectException(FileException::class);
        $uploader->receiveChunk('test-session-5', 'test-bad.exe', false);
    }

    public function testResumableUploadExceedsSizeLimit() {
        $inputFile = $this->tmpDir . '/chunk-large.txt';
        file_put_contents($inputFile, str_repeat('x', 2048));

        $uploader = new ResumableUploader($this->uploadDir, ['txt'], $inputFile);
        $uploader->setMaxFileSize(1024);

        $this->expectException(FileException::class);
        $uploader->receiveChunk('test-session-6', 'test-too-big.txt', false);
    }

    public function testResumableUploadCleanStale() {
        // Create a partial manually
        $partialDir = $this->uploadDir . '/.partial';
        if (!is_dir($partialDir)) {
            mkdir($partialDir, 0755, true);
        }
        $partialFile = $partialDir . '/old-session_test-stale.txt';
        file_put_contents($partialFile, 'stale data');
        touch($partialFile, time() - 7200); // 2 hours old

        $uploader = new ResumableUploader($this->uploadDir, ['txt']);
        $removed = $uploader->cleanStale(3600); // remove older than 1 hour

        $this->assertEquals(1, $removed);
        $this->assertFileDoesNotExist($partialFile);
    }
}
