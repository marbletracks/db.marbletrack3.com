<?php
/**
 * Test database configuration verification using Base::getTestDB()
 */

require_once __DIR__ . '/../prepend.php';

echo "=== Test Database Configuration Check ===\n\n";

try {
    // Test production database connection
    $prodConfig = new Config();
    $prodDb = \Database\Base::getDB($prodConfig);
    
    echo "Production Database:\n";
    $prodResult = $prodDb->executeSQL("SELECT DATABASE() as current_db");
    if ($prodResult && $prodResult->num_rows > 0) {
        $row = $prodResult->fetch_assoc();
        $prodDbName = $row['current_db'];
        echo "  Connected to: '$prodDbName'\n";
    } else {
        echo "  Could not determine production database name\n";
        $prodDbName = $prodConfig->dbName ?: 'unknown';
    }
    echo "  Host: '" . $prodConfig->dbHost . "'\n";
    echo "  User: '" . $prodConfig->dbUser . "'\n\n";
    
    // Test test database connection using Base::getTestDB()
    require_once __DIR__ . '/../tests/bootstrap.php';
    $testConfig = getTestConfig();
    $testDb = \Database\Base::getTestDB($testConfig);
    
    echo "Test Database:\n";
    $testResult = $testDb->executeSQL("SELECT DATABASE() as current_db");
    if ($testResult && $testResult->num_rows > 0) {
        $row = $testResult->fetch_assoc();
        $testDbName = $row['current_db'];
        echo "  Connected to: '$testDbName'\n";
    } else {
        echo "  Could not determine test database name\n";
        $testDbName = $testConfig->dbName ?: 'unknown';
    }
    echo "  Host: '" . $testConfig->dbHost . "'\n";
    echo "  User: '" . $testConfig->dbUser . "'\n\n";
    
    // Validation
    if ($testDbName === $prodDbName && !empty($prodDbName) && $prodDbName !== 'unknown') {
        echo "❌ ERROR: Test and production are using the same database!\n";
        echo "   Production: $prodDbName\n";
        echo "   Test: $testDbName\n";
        echo "   This would cause tests to write to production data!\n";
    } else if ($testDbName === 'dbmt3_test') {
        echo "✅ SUCCESS: Test database correctly configured as 'dbmt3_test'\n";
        echo "   Production: $prodDbName\n";
        echo "   Test: $testDbName\n";
        echo "   Tests will safely use separate database\n";
    } else {
        echo "⚠️  Test database name: '$testDbName'\n";
        echo "   Production database: '$prodDbName'\n";
        echo "   Expected test database: 'dbmt3_test' (for Dreamhost)\n";
    }
    
    // Test basic operations on test database
    echo "\n=== Test Database Operations ===\n";
    try {
        // Check if basic tables exist
        $tablesResult = $testDb->executeSQL("SHOW TABLES");
        $tableCount = $tablesResult ? $tablesResult->num_rows : 0;
        echo "  Tables in test database: $tableCount\n";
        
        if ($tableCount === 0) {
            echo "  ⚠️  Test database appears empty - run setup_test_database.php to sync with production\n";
        } else {
            // Check for specific tables we need for tests
            $partsResult = $testDb->executeSQL("SHOW TABLES LIKE 'parts'");
            if ($partsResult && $partsResult->num_rows > 0) {
                echo "  ✅ 'parts' table exists\n";
            } else {
                echo "  ❌ 'parts' table missing - tests will fail\n";
            }
            
            $workersResult = $testDb->executeSQL("SHOW TABLES LIKE 'workers'");
            if ($workersResult && $workersResult->num_rows > 0) {
                echo "  ✅ 'workers' table exists\n";
            } else {
                echo "  ❌ 'workers' table missing - tests will fail\n";
            }
        }
        
    } catch (Exception $e) {
        echo "  ❌ Error checking test database: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Recommendations ===\n";
    if ($tableCount === 0) {
        echo "To set up test database:\n";
        echo "  php scripts/setup_test_database.php setup\n\n";
    }
    echo "To run tests:\n";
    echo "  php composer.phar run test\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nThis usually means the database connection failed.\n";
    echo "Check your Config.php settings or run setup_test_database.php\n";
}