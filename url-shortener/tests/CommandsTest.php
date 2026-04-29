<?php
namespace Tests;

use App\Commands\CleanupLinksCommand;
use App\Commands\LinkStatsCommand;
use App\Commands\ListLinksCommand;
use WebFiori\Cli\CommandTestCase;

/**
 * Tests for the custom CLI commands.
 */
class CommandsTest extends CommandTestCase {
    public function testListLinks() {
        $output = $this->executeSingleCommand(new ListLinksCommand());
        $this->assertEquals(0, $this->getExitCode());
        $outputStr = implode("\n", $output);
        $this->assertStringContainsString('abc123', $outputStr);
        $this->assertStringContainsString('webfiori.com', $outputStr);
        $this->assertStringContainsString('Total:', $outputStr);
    }

    public function testLinkStats() {
        $output = $this->executeSingleCommand(new LinkStatsCommand());
        $this->assertEquals(0, $this->getExitCode());
        $outputStr = implode("\n", $output);
        $this->assertStringContainsString('Total links:', $outputStr);
        $this->assertStringContainsString('Total clicks:', $outputStr);
    }

    public function testCleanupNoExpired() {
        $output = $this->executeSingleCommand(new CleanupLinksCommand());
        $this->assertEquals(0, $this->getExitCode());
        $outputStr = implode("\n", $output);
        $this->assertStringContainsString('Removed 0 expired link(s)', $outputStr);
    }
}
