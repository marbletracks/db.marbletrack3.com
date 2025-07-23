<?php
/**
 * Test bootstrap file
 * Sets up the testing environment for Marble Track 3
 */

// Include the main application bootstrap
require_once __DIR__ . '/../prepend.php';

// Test-specific configuration
class TestConfig extends Config {
    public function __construct() {
        parent::__construct();
        
        // Override database settings for testing
        // These would be set to test database credentials
        // For now, we'll use the same structure but different database name
        $this->dbName = $this->dbName . '_test'; // e.g., marbletrack3_test
        
        // Test-specific settings
        $this->app_path = __DIR__ . '/..';
    }
}

// Helper function to get test database connection
function getTestDatabase(): \Database\Database {
    static $testDb = null;
    
    if ($testDb === null) {
        $testConfig = new TestConfig();
        $testDb = \Database\Base::getDB($testConfig);
    }
    
    return $testDb;
}

// Helper function to get test config
function getTestConfig(): TestConfig {
    return new TestConfig();
}