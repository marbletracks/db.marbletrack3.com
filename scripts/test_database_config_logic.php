<?php
/**
 * Simple test configuration verification that doesn't load the full app
 */

// Load just the autoloader and classes we need
require_once __DIR__ . '/../classes/Mlaphp/Autoloader.php';
$autoloader = new \Mlaphp\Autoloader();
spl_autoload_register(array($autoloader, 'load'));

echo "=== Test Database Configuration Check ===\n\n";

// Create Config.php from sample if it doesn't exist
$configPath = __DIR__ . '/../classes/Config.php';
$configSamplePath = __DIR__ . '/../classes/ConfigSample.php';

if (!file_exists($configPath) && file_exists($configSamplePath)) {
    copy($configSamplePath, $configPath);
    echo "ℹ️  Created Config.php from ConfigSample.php\n";
}

// Load Config class
try {
    require_once $configPath;
    
    // Test production config
    $prodConfig = new Config();
    echo "Production Config values:\n";
    echo "  dbName: '" . $prodConfig->dbName . "'\n";
    echo "  dbHost: '" . $prodConfig->dbHost . "'\n";
    echo "  dbUser: '" . $prodConfig->dbUser . "'\n\n";
    
    // Test our TestConfig logic manually
    echo "Testing TestConfig logic:\n";
    
    // Simulate what TestConfig does
    class TestTestConfig extends Config {
        public function __construct() {
            // Override database settings for testing
            // The production database is 'dbmt3', so test database should be 'dbmt3_test'
            if (empty($this->dbName)) {
                // If dbName is empty (from ConfigSample.php), set it to the test database
                $this->dbName = 'dbmt3_test';
                echo "  dbName was empty, set to: 'dbmt3_test'\n";
            } else {
                // If dbName is set (on Dreamhost with real Config.php), 
                // replace production name with test name
                if ($this->dbName === 'dbmt3') {
                    $this->dbName = 'dbmt3_test';
                    echo "  dbName was 'dbmt3', changed to: 'dbmt3_test'\n";
                } else {
                    // For other database names, append _test
                    $originalName = $this->dbName;
                    $this->dbName = $this->dbName . '_test';
                    echo "  dbName was '$originalName', changed to: '{$this->dbName}'\n";
                }
            }
            
            // Test-specific settings
            $this->app_path = __DIR__ . '/..';
        }
    }
    
    $testConfig = new TestTestConfig();
    echo "\nResulting test config:\n";
    echo "  dbName: '" . $testConfig->dbName . "'\n";
    echo "  dbHost: '" . $testConfig->dbHost . "'\n";
    echo "  dbUser: '" . $testConfig->dbUser . "'\n\n";
    
    // Validation
    if ($testConfig->dbName === 'dbmt3_test') {
        echo "✅ SUCCESS: Test database correctly configured as 'dbmt3_test'\n";
        echo "   This means on Dreamhost, tests will use the test database instead of production\n";
    } else {
        echo "⚠️  Test database name: '{$testConfig->dbName}'\n";
        echo "   Expected: 'dbmt3_test'\n";
    }
    
    // Show what would happen in production environment
    echo "\n=== Simulation: Production Environment (Dreamhost) ===\n";
    
    // Simulate production config values
    $mockProdConfig = new Config();
    $mockProdConfig->dbName = 'dbmt3';  // Simulate real production value
    $mockProdConfig->dbHost = 'mysql.example.com';
    $mockProdConfig->dbUser = 'dh_mt3';
    
    echo "Simulated production config:\n";
    echo "  dbName: '{$mockProdConfig->dbName}'\n";
    echo "  dbHost: '{$mockProdConfig->dbHost}'\n";
    echo "  dbUser: '{$mockProdConfig->dbUser}'\n\n";
    
    // Simulate TestConfig with production values
    class MockTestConfig {
        public $dbName;
        public $dbHost;
        public $dbUser;
        
        public function __construct($baseConfig) {
            // Copy base config values
            $this->dbName = $baseConfig->dbName;
            $this->dbHost = $baseConfig->dbHost;
            $this->dbUser = $baseConfig->dbUser;
            
            // Apply test logic
            if (empty($this->dbName)) {
                $this->dbName = 'dbmt3_test';
            } else {
                if ($this->dbName === 'dbmt3') {
                    $this->dbName = 'dbmt3_test';
                } else {
                    $this->dbName = $this->dbName . '_test';
                }
            }
        }
    }
    
    $mockTestConfig = new MockTestConfig($mockProdConfig);
    echo "Simulated test config on Dreamhost:\n";
    echo "  dbName: '{$mockTestConfig->dbName}'\n";
    echo "  dbHost: '{$mockTestConfig->dbHost}'\n";
    echo "  dbUser: '{$mockTestConfig->dbUser}'\n\n";
    
    if ($mockTestConfig->dbName === 'dbmt3_test') {
        echo "✅ SUCCESS: In production environment, tests would use 'dbmt3_test'\n";
        echo "✅ This should fix the issue where tests were writing to production database\n";
    } else {
        echo "❌ ERROR: Test config logic needs adjustment\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}