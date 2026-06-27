<?php
namespace Tests;

use App\Apis\UploadService;
use WebFiori\Http\Test\ServiceTestCase;

/**
 * Tests for the standard multipart upload service.
 */
class UploadServiceTest extends ServiceTestCase {

    public function testListFilesEmpty() {
        $this->get(new UploadService())
            ->assertOk()
            ->assertJsonHas('data');
    }
}
