<?php
/**
 * Test bootstrap file
 * Sets up the testing environment for Marble Track 3
 */

// Check if this is running in a testing environment where Config.php might not exist
$configPath = __DIR__ . '/../classes/Config.php';
$configSamplePath = __DIR__ . '/../classes/ConfigSample.php';

// If Config.php doesn't exist but ConfigSample.php does, create a temporary test config
if (!file_exists($configPath) && file_exists($configSamplePath)) {
    // Create a minimal test config
    copy($configSamplePath, $configPath);
    
    // Mark that we created it so we can clean up later if needed
    $testConfigCreated = true;
}

try {
    // Include the main application bootstrap
    require_once __DIR__ . '/../prepend.php';
} catch (Exception $e) {
    // If bootstrap fails, at least load the autoloader for unit tests
    require_once __DIR__ . '/../classes/Mlaphp/Autoloader.php';
    $autoloader = new \Mlaphp\Autoloader();
    spl_autoload_register(array($autoloader, 'load'));
    
    echo "Warning: Could not load full application bootstrap, running in minimal mode for unit tests only.\n";
    echo "Error: " . $e->getMessage() . "\n";
}

// Test-specific configuration
if (class_exists('Config')) {
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
} else {
    // Minimal functions for unit tests when database is not available
    function getTestDatabase() {
        throw new Exception("Database testing not available in minimal mode");
    }
    
    function getTestConfig() {
        throw new Exception("Config testing not available in minimal mode");
    }
}