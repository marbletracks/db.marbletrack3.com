<?php
/**
 * Script to verify that test configuration is pointing to the correct database
 * Uses Base::getTestDB() to get the actual test database connection
 */

require_once __DIR__ . '/../prepend.php';

echo "=== Database Configuration Verification ===\n\n";

try {
    // Get production database connection
    $prodConfig = new Config();
    $prodDb = \Database\Base::getDB($prodConfig);
    
    echo "Production Database:\n";
    $prodResult = $prodDb->executeSQL("SELECT DATABASE() as current_db");
    if ($prodResult && $prodResult->num_rows > 0) {
        $row = $prodResult->fetch_assoc();
        $prodDbName = $row['current_db'];
        echo "  Database Name: '$prodDbName'\n";
    } else {
        $prodDbName = $prodConfig->dbName ?: 'unknown';
        echo "  Database Name: '$prodDbName' (from config)\n";
    }
    echo "  Database Host: '" . $prodConfig->dbHost . "'\n";
    echo "  Database User: '" . $prodConfig->dbUser . "'\n\n";
    
    // Get test database connection using Base::getTestDB()
    require_once __DIR__ . '/../tests/bootstrap.php';
    $testConfig = getTestConfig();
    $testDb = \Database\Base::getTestDB($testConfig);
    
    echo "Test Database:\n";
    $testResult = $testDb->executeSQL("SELECT DATABASE() as current_db");
    if ($testResult && $testResult->num_rows > 0) {
        $row = $testResult->fetch_assoc();
        $testDbName = $row['current_db'];
        echo "  Database Name: '$testDbName'\n";
    } else {
        $testDbName = $testConfig->dbName ?: 'unknown';
        echo "  Database Name: '$testDbName' (from config)\n";
    }
    echo "  Database Host: '" . $testConfig->dbHost . "'\n";
    echo "  Database User: '" . $testConfig->dbUser . "'\n\n";
    
    // Check if this is a development environment (empty config values)
    if (empty($prodConfig->dbName) && empty($prodConfig->dbHost)) {
        echo "ℹ️  Development environment detected (empty config values)\n";
        echo "   In this environment, tests would need actual database credentials\n";
        echo "   On Dreamhost, the real Config.php has actual values\n\n";
    }
    
    // Verify test database name is different and correct
    if ($testDbName === $prodDbName && !empty($prodDbName) && $prodDbName !== 'unknown') {
        echo "❌ ERROR: Test database name is the same as production!\n";
        echo "   Production: {$prodDbName}\n";
        echo "   Test: {$testDbName}\n";
        echo "   This would cause tests to write to production data!\n";
        exit(1);
    }
    
    if (empty($testDbName) || $testDbName === 'unknown') {
        echo "❌ ERROR: Test database name could not be determined!\n";
        exit(1);
    }
    
    if ($testDbName === 'dbmt3_test') {
        echo "✅ Test database correctly set to 'dbmt3_test'\n";
    } else {
        echo "⚠️  Test database name: '{$testDbName}' (expected 'dbmt3_test' for production environment)\n";
    }
    
    // Test database operations
    echo "\n=== Database Connection Test ===\n";
    try {
        // Test basic operations on test database
        $testDb->executeSQL("SELECT 1");
        echo "✅ Test database connection established successfully\n";
        
        // Check tables exist
        $tablesResult = $testDb->executeSQL("SHOW TABLES");
        $tableCount = $tablesResult ? $tablesResult->num_rows : 0;
        echo "✅ Test database contains $tableCount tables\n";
        
        if ($tableCount === 0) {
            echo "⚠️  Test database is empty - run 'php scripts/setup_test_database.php setup'\n";
        } else {
            // Check for required tables
            $partsCheck = $testDb->executeSQL("SHOW TABLES LIKE 'parts'");
            if ($partsCheck && $partsCheck->num_rows > 0) {
                echo "✅ Required 'parts' table exists\n";
            } else {
                echo "❌ Missing 'parts' table - tests will fail\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Failed to test database operations: " . $e->getMessage() . "\n";
        echo "   Check database credentials and permissions\n";
    }
    
    echo "\n=== Configuration Summary ===\n";
    echo "Production DB: '$prodDbName'\n";
    echo "Test DB: '$testDbName'\n";
    
    if ($testDbName === 'dbmt3_test' && $testDbName !== $prodDbName) {
        echo "✅ Test configuration is correctly isolated from production!\n";
    } else {
        echo "⚠️  Test configuration may need adjustment for production environment\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during verification: " . $e->getMessage() . "\n";
    echo "   This usually indicates a configuration or connection problem\n";
    exit(1);
}