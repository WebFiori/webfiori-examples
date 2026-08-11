<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use WebFiori\File\Exceptions\FileException;
use WebFiori\File\ResumableUploader;
use WebFiori\File\StreamingUploader;
use WebFiori\File\UploadedFile;

/**
 * Deep dive tests: stream processors, checksums, encryption, setPartialDir,
 * cleanStale, before/after callbacks, and testing with $inputSource.
 */
class FileUploadDeepDiveTest extends TestCase {
    private string $uploadDir;
    private string $partialDir;
    private string $tmpDir;

    protected function setUp(): void {
        $this->tmpDir    = sys_get_temp_dir() . '/wf-upload-test-' . uniqid();
        $this->uploadDir = $this->tmpDir . '/uploads';
        $this->partialDir = $this->tmpDir . '/partials';
        mkdir($this->uploadDir, 0755, true);
        mkdir($this->partialDir, 0755, true);
    }

    protected function tearDown(): void {
        $this->removeDir($this->tmpDir);
    }

    private function removeDir(string $dir): void {
        if (!is_dir($dir)) {
            return;
        }

        foreach (glob($dir . '/{,.}*', GLOB_BRACE) as $entry) {
            if (basename($entry) === '.' || basename($entry) === '..') {
                continue;
            }
            is_dir($entry) ? $this->removeDir($entry) : unlink($entry);
        }
        rmdir($dir);
    }

    private function makeTempInput(string $content): string {
        $path = $this->tmpDir . '/input-' . uniqid();
        file_put_contents($path, $content);
        return $path;
    }

    // --- Stream processor: SHA-256 checksum ---

    public function testStreamProcessorComputesChecksum(): void {
        $content  = str_repeat('Hello WebFiori!', 100);
        $input    = $this->makeTempInput($content);
        $checksum = null;

        $uploader = new StreamingUploader($this->uploadDir, ['txt'], $input);
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

        $file = $uploader->receive('report.txt');

        $this->assertNotNull($checksum);
        $this->assertEquals(hash('sha256', $content), $checksum);
        $this->assertFileExists($this->uploadDir . '/report.txt');
        $this->assertEquals($content, file_get_contents($this->uploadDir . '/report.txt'));
    }

    // --- Stream processor: AES-256-CTR encryption during upload ---

    public function testStreamProcessorEncryptsFileOnDisk(): void {
        $content  = 'Sensitive document contents that must be encrypted at rest.';
        $input    = $this->makeTempInput($content);
        $key      = random_bytes(32);
        $iv       = random_bytes(16);

        $uploader = new StreamingUploader($this->uploadDir, ['txt'], $input);
        $uploader->setStreamProcessor(function (\Generator $chunks, string $destPath) use ($key, $iv) {
            $dest = fopen($destPath, 'wb');

            // Write IV as first 16 bytes so we can decrypt later
            fwrite($dest, $iv);

            foreach ($chunks as $chunk) {
                $encrypted = openssl_encrypt($chunk, 'aes-256-ctr', $key, OPENSSL_RAW_DATA, $iv);
                fwrite($dest, $encrypted);
            }

            fclose($dest);
        });

        $file = $uploader->receive('secret.txt');

        $stored = file_get_contents($this->uploadDir . '/secret.txt');

        // The stored file is not plaintext
        $this->assertNotEquals($content, substr($stored, 16));

        // But we can decrypt it back
        $storedIv  = substr($stored, 0, 16);
        $cipher    = substr($stored, 16);
        $decrypted = openssl_decrypt($cipher, 'aes-256-ctr', $key, OPENSSL_RAW_DATA, $storedIv);

        $this->assertEquals($content, $decrypted);
    }

    // --- Stream processor on ResumableUploader ---

    public function testResumableUploaderStreamProcessorOnFinalChunk(): void {
        $content  = 'Final chunk content after reassembly.';
        $checksum = null;

        $uploader = new ResumableUploader($this->uploadDir, ['txt'], $this->makeTempInput('First chunk. '));
        $uploader->setPartialDir($this->partialDir);
        $uploader->receiveChunk('upload-123', 'document.txt', false);

        // Set stream processor before the final chunk
        $uploader = new ResumableUploader($this->uploadDir, ['txt'], $this->makeTempInput($content));
        $uploader->setPartialDir($this->partialDir);
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

        $result = $uploader->receiveChunk('upload-123', 'document.txt', true);

        $this->assertTrue($result['complete']);
        $this->assertNotNull($checksum);
        $this->assertFileExists($this->uploadDir . '/document.txt');
    }

    // --- setPartialDir separates partial from final storage ---

    public function testSetPartialDirKeepsPartialsOutOfUploadDir(): void {
        $content = str_repeat('chunk', 200);
        $input   = $this->makeTempInput($content);

        $uploader = new ResumableUploader($this->uploadDir, ['txt'], $input);
        $uploader->setPartialDir($this->partialDir);

        $result = $uploader->receiveChunk('upload-abc', 'video.txt', false);

        // Partial file is in $partialDir, not in $uploadDir
        $partials = glob($this->partialDir . '/*');
        $finals   = glob($this->uploadDir . '/*.txt');

        $this->assertNotEmpty($partials);
        $this->assertEmpty($finals);
    }

    // --- cleanStale removes old partial files ---

    public function testCleanStaleRemovesAbandonedPartials(): void {
        $uploader = new ResumableUploader($this->uploadDir, ['txt'], $this->makeTempInput('chunk1'));
        $uploader->setPartialDir($this->partialDir);
        $uploader->receiveChunk('stale-upload', 'abandoned.txt', false);

        $partials = glob($this->partialDir . '/*');
        $this->assertCount(1, $partials);

        // Touch the file to make it appear older than 1 second
        touch($partials[0], time() - 10);

        $removed = $uploader->cleanStale(5); // remove files older than 5 seconds

        $this->assertEquals(1, $removed);
        $this->assertCount(0, glob($this->partialDir . '/*'));
    }

    public function testCleanStalePreservesRecentPartials(): void {
        $uploader = new ResumableUploader($this->uploadDir, ['txt'], $this->makeTempInput('chunk'));
        $uploader->setPartialDir($this->partialDir);
        $uploader->receiveChunk('active-upload', 'active.txt', false);

        // cleanStale with 1 hour threshold — recent file should survive
        $removed = $uploader->cleanStale(3600);

        $this->assertEquals(0, $removed);
        $this->assertCount(1, glob($this->partialDir . '/*'));
    }

    // --- Before-upload callback: quota enforcement ---

    public function testBeforeUploadCallbackCanRejectUpload(): void {
        $input    = $this->makeTempInput('File content');
        $uploader = new StreamingUploader($this->uploadDir, ['txt'], $input);

        $uploader->setOnBeforeUpload(function (array $fileInfo): bool {
            // Reject files with names containing 'blocked'
            return !str_contains($fileInfo['name'], 'blocked');
        });

        $this->expectException(FileException::class);
        $uploader->receive('blocked-file.txt');
    }

    public function testBeforeUploadCallbackAllowsValidUpload(): void {
        $input    = $this->makeTempInput('Allowed content');
        $uploader = new StreamingUploader($this->uploadDir, ['txt'], $input);
        $allowed  = false;

        $uploader->setOnBeforeUpload(function (array $fileInfo) use (&$allowed): bool {
            $allowed = true;
            return true;
        });

        $file = $uploader->receive('allowed-file.txt');

        $this->assertTrue($allowed);
        $this->assertTrue($file->isUploaded());
    }

    // --- After-upload callback: database record, notifications ---

    public function testAfterUploadCallbackReceivesUploadedFile(): void {
        $input    = $this->makeTempInput('Document content');
        $uploader = new StreamingUploader($this->uploadDir, ['txt'], $input);

        $capturedFile = null;
        $uploader->setOnAfterUpload(function (UploadedFile $file) use (&$capturedFile): void {
            $capturedFile = $file;
        });

        $uploader->receive('document.txt');

        $this->assertNotNull($capturedFile);
        $this->assertEquals('document.txt', $capturedFile->getName());
    }

    // --- Extension filtering ---

    public function testRejectsDisallowedExtension(): void {
        $input    = $this->makeTempInput('<?php echo "evil"; ?>');
        $uploader = new StreamingUploader($this->uploadDir, ['pdf', 'docx'], $input);

        $this->expectException(FileException::class);
        $uploader->receive('malicious.php');
    }

    public function testAllowsAllExtensionsWhenNoneConfigured(): void {
        $input    = $this->makeTempInput('content');
        $uploader = new StreamingUploader($this->uploadDir, [], $input);

        $file = $uploader->receive('anything.exe');

        $this->assertTrue($file->isUploaded());
    }

    // --- Resumable: offset tracking ---

    public function testGetOffsetReturnsZeroForNewUpload(): void {
        $uploader = new ResumableUploader($this->uploadDir, ['txt']);
        $uploader->setPartialDir($this->partialDir);

        $this->assertEquals(0, $uploader->getOffset('new-upload', 'file.txt'));
    }

    public function testGetOffsetReflectsReceivedBytes(): void {
        $content  = str_repeat('x', 1000);
        $uploader = new ResumableUploader($this->uploadDir, ['txt'], $this->makeTempInput($content));
        $uploader->setPartialDir($this->partialDir);
        $uploader->receiveChunk('track-upload', 'progress.txt', false);

        $offset = $uploader->getOffset('track-upload', 'progress.txt');
        $this->assertEquals(1000, $offset);
    }

    // --- Cancel cleans up partial file ---

    public function testCancelRemovesPartialFile(): void {
        $uploader = new ResumableUploader($this->uploadDir, ['txt'], $this->makeTempInput('chunk'));
        $uploader->setPartialDir($this->partialDir);
        $uploader->receiveChunk('cancel-upload', 'to-cancel.txt', false);

        $uploader->cancel('cancel-upload', 'to-cancel.txt');

        $this->assertCount(0, glob($this->partialDir . '/*'));
    }

    // --- Size limit enforcement ---

    public function testStreamingUploaderRejectsOversizedFile(): void {
        $content  = str_repeat('x', 1000);
        $input    = $this->makeTempInput($content);
        $uploader = new StreamingUploader($this->uploadDir, ['txt'], $input);
        $uploader->setMaxFileSize(500); // 500 bytes max

        $this->expectException(FileException::class);
        $uploader->receive('big-file.txt');
    }

    // --- Filename sanitization ---

    public function testSanitizesFilenamePathTraversal(): void {
        // basename() strips directory traversal, leaving only the final component
        $sanitized = StreamingUploader::sanitizeFilename('../../../etc/passwd');
        $this->assertEquals('passwd', $sanitized);
    }

    public function testSanitizesFilenameNullBytes(): void {
        $sanitized = StreamingUploader::sanitizeFilename("file\0name.txt");
        $this->assertEquals('filename.txt', $sanitized);
    }
}
