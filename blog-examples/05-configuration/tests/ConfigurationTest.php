<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\App;

class ConfigurationTest extends TestCase {

    // ========== App Config Access ==========

    public function testGetVersion() {
        $config = App::getConfig();
        $this->assertEquals('1.0.0', $config->getAppVersion());
    }

    public function testGetVersionType() {
        $config = App::getConfig();
        $this->assertEquals('Stable', $config->getAppVersionType());
    }

    public function testGetAppName() {
        $config = App::getConfig();
        $this->assertEquals('Configuration Demo', $config->getAppName('EN'));
    }

    public function testGetDescription() {
        $config = App::getConfig();
        $this->assertStringContainsString('Configuration', $config->getDescription('EN'));
    }

    public function testGetPrimaryLanguage() {
        $config = App::getConfig();
        $this->assertEquals('EN', $config->getPrimaryLanguage());
    }

    public function testGetTitleSeparator() {
        $config = App::getConfig();
        $this->assertEquals('|', $config->getTitleSeparator());
    }

    public function testGetReleaseDate() {
        $config = App::getConfig();
        $this->assertEquals('2026-07-21', $config->getAppReleaseDate());
    }

    // ========== Environment Variables as Constants ==========

    public function testWfVerboseConstant() {
        $this->assertTrue(defined('WF_VERBOSE'));
        $this->assertTrue(WF_VERBOSE);
    }

    public function testCliHttpHostConstant() {
        $this->assertTrue(defined('CLI_HTTP_HOST'));
        $this->assertEquals('127.0.0.1', CLI_HTTP_HOST);
    }

    public function testCustomEnvVarAppEnv() {
        $this->assertTrue(defined('APP_ENV'));
        $this->assertEquals('development', APP_ENV);
    }

    public function testCustomEnvVarMaxUploadSize() {
        $this->assertTrue(defined('MAX_UPLOAD_SIZE'));
        $this->assertEquals(10485760, MAX_UPLOAD_SIZE);
    }

    public function testCustomEnvVarApiRateLimit() {
        $this->assertTrue(defined('API_RATE_LIMIT'));
        $this->assertEquals(100, API_RATE_LIMIT);
    }

    // ========== Database Connection ==========

    public function testDbConnectionExists() {
        $config = App::getConfig();
        $conn = $config->getDBConnection('app-db');
        $this->assertNotNull($conn);
    }

    public function testDbConnectionType() {
        $config = App::getConfig();
        $conn = $config->getDBConnection('app-db');
        $this->assertEquals('sqlite', $conn->getDatabaseType());
    }

    public function testDbConnectionName() {
        $config = App::getConfig();
        $conn = $config->getDBConnection('app-db');
        $this->assertEquals('app.db', $conn->getDBName());
    }

    public function testNonExistentDbConnection() {
        $config = App::getConfig();
        $conn = $config->getDBConnection('non-existent');
        $this->assertNull($conn);
    }

    // ========== Multiple Connections ==========

    public function testGetAllConnections() {
        $config = App::getConfig();
        $connections = $config->getDBConnections();
        $this->assertArrayHasKey('app-db', $connections);
    }
}
