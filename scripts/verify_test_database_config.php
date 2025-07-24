<?php
/**
 * Script to verify that test configuration is pointing to the correct database
 */

// Include test bootstrap
require_once __DIR__ . '/../tests/bootstrap.php';

echo "=== Database Configuration Verification ===\n\n";

try {
    // Check if we have Config class available
    if (!class_exists('Config')) {
        echo "❌ Config class not available\n";
        exit(1);
    }
    
    // Create production config
    $prodConfig = new Config();
    echo "Production Config:\n";
    echo "  Database Name: '" . $prodConfig->dbName . "'\n";
    echo "  Database Host: '" . $prodConfig->dbHost . "'\n";
    echo "  Database User: '" . $prodConfig->dbUser . "'\n\n";
    
    // Create test config
    $testConfig = getTestConfig();
    echo "Test Config:\n";
    echo "  Database Name: '" . $testConfig->dbName . "'\n";
    echo "  Database Host: '" . $testConfig->dbHost . "'\n";
    echo "  Database User: '" . $testConfig->dbUser . "'\n\n";
    
    // Check if this is a development environment (empty config values)
    if (empty($prodConfig->dbName) && empty($prodConfig->dbHost)) {
        echo "ℹ️  Development environment detected (empty config values)\n";
        echo "   In this environment, tests would need actual database credentials\n";
        echo "   On Dreamhost, the real Config.php has actual values\n\n";
    }
    
    // Verify test database name is different and correct
    if ($testConfig->dbName === $prodConfig->dbName && !empty($prodConfig->dbName)) {
        echo "❌ ERROR: Test database name is the same as production!\n";
        echo "   Production: {$prodConfig->dbName}\n";
        echo "   Test: {$testConfig->dbName}\n";
        exit(1);
    }
    
    if (empty($testConfig->dbName)) {
        echo "❌ ERROR: Test database name is empty!\n";
        exit(1);
    }
    
    if ($testConfig->dbName === 'dbmt3_test') {
        echo "✅ Test database correctly set to 'dbmt3_test'\n";
    } else {
        echo "⚠️  Test database name: '{$testConfig->dbName}' (expected 'dbmt3_test' for production environment)\n";
    }
    
    // Try to get test database connection only if we have database credentials
    if (!empty($testConfig->dbHost) && !empty($testConfig->dbUser) && !empty($testConfig->dbName)) {
        echo "\n=== Database Connection Test ===\n";
        try {
            $testDb = getTestDatabase();
            echo "✅ Test database connection established successfully\n";
            
            // Try a simple query to verify we're connected to the right database
            $result = $testDb->executeSQL("SELECT DATABASE() as current_db");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $currentDb = $row['current_db'];
                echo "✅ Connected to database: '$currentDb'\n";
                
                if ($currentDb === 'dbmt3_test') {
                    echo "✅ SUCCESS: Connected to correct test database!\n";
                } elseif ($currentDb === 'dbmt3') {
                    echo "❌ ERROR: Connected to production database instead of test database!\n";
                    exit(1);
                } else {
                    echo "⚠️  Connected to unexpected database: '$currentDb'\n";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Failed to connect to test database: " . $e->getMessage() . "\n";
            echo "   This is expected in development environment without real DB credentials\n";
        }
    } else {
        echo "\n=== Database Connection Test ===\n";
        echo "ℹ️  Skipping database connection test (no credentials available)\n";
        echo "   This is normal in development environment\n";
        echo "   On Dreamhost with real Config.php, this test should work\n";
    }
    
    echo "\n=== Configuration Summary ===\n";
    echo "Production DB: '" . ($prodConfig->dbName ?: 'empty') . "'\n";
    echo "Test DB: '" . ($testConfig->dbName ?: 'empty') . "'\n";
    
    if ($testConfig->dbName === 'dbmt3_test') {
        echo "✅ Test configuration is correctly set to use test database!\n";
    } else {
        echo "⚠️  Test configuration may need adjustment for production environment\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during verification: " . $e->getMessage() . "\n";
    exit(1);
}