<?php
/**
 * Minimal test bootstrap for Docker environment
 * Avoids loading full application to prevent path issues
 */

// Load only what's needed for testing
require_once __DIR__ . '/../classes/Mlaphp/Autoloader.php';
$autoloader = new \Mlaphp\Autoloader();
spl_autoload_register(array($autoloader, 'load'));

// Use the actual Config class but override the path for Docker
class TestConfig extends Config {
    public function __construct() {
        // Fix Docker path issue - use /app instead of hardcoded production path
        $this->app_path = '/app';
    }
}

// Helper function to get test database connection
function getTestDatabase(): \Database\Database {
    static $testDb = null;
    
    if ($testDb === null) {
        $testConfig = new TestConfig();
        $testDb = \Database\Base::getTestDB($testConfig);
    }
    
    return $testDb;
}

// Helper function to get test config
function getTestConfig(): TestConfig {
    return new TestConfig();
}