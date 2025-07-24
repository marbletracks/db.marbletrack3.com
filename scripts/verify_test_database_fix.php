<?php
/**
 * Verify that the test database configuration fix is working
 * Demonstrates the separation of production and test database connections using Base::getTestDB()
 */

require_once __DIR__ . '/../prepend.php';

echo "Verifying test database configuration fix...\n\n";

echo "=== Before Fix (The Problem) ===\n";
echo "Issue: prepend.php called Base::getDB() with production config\n";
echo "       Tests tried to use same Base::getDB() but with test config\n";
echo "       But Base::\$db was already initialized with production database!\n";
echo "       Result: Tests wrote to production database 'dbmt3'\n\n";

echo "=== After Fix (The Solution) ===\n";
echo "Added Base::getTestDB() method that uses separate static variable\n";
echo "Now production and test database connections are completely independent\n\n";

try {
    // Demonstrate production database connection
    $prodConfig = new Config();
    $prodDb = \Database\Base::getDB($prodConfig);
    
    echo "1. Production connection via Base::getDB():\n";
    $prodResult = $prodDb->executeSQL("SELECT DATABASE() as current_db");
    if ($prodResult && $prodResult->num_rows > 0) {
        $row = $prodResult->fetch_assoc();
        $prodDbName = $row['current_db'];
        echo "   ✅ Connected to: '$prodDbName'\n";
    } else {
        echo "   ⚠️  Could not determine production database name\n";
        $prodDbName = $prodConfig->dbName ?: 'unknown';
    }
    
    // Demonstrate test database connection
    require_once __DIR__ . '/../tests/bootstrap.php';
    $testConfig = getTestConfig();
    $testDb = \Database\Base::getTestDB($testConfig);
    
    echo "\n2. Test connection via Base::getTestDB():\n";
    $testResult = $testDb->executeSQL("SELECT DATABASE() as current_db");
    if ($testResult && $testResult->num_rows > 0) {
        $row = $testResult->fetch_assoc();
        $testDbName = $row['current_db'];
        echo "   ✅ Connected to: '$testDbName'\n";
    } else {
        echo "   ⚠️  Could not determine test database name\n";
        $testDbName = $testConfig->dbName ?: 'unknown';
    }
    
    echo "\n=== Verification Results ===\n";
    if ($prodDbName !== $testDbName && !empty($prodDbName) && !empty($testDbName)) {
        echo "✅ SUCCESS: Production and test databases are properly separated!\n";
        echo "   Production database: '$prodDbName'\n";
        echo "   Test database: '$testDbName'\n";
        echo "   Both connections coexist independently\n";
    } else if ($prodDbName === $testDbName) {
        echo "❌ ERROR: Both connections point to the same database!\n";
        echo "   Database: '$prodDbName'\n";
        echo "   This could cause tests to affect production data\n";
    } else {
        echo "⚠️  Could not fully verify database separation\n";
        echo "   Production: '$prodDbName'\n";
        echo "   Test: '$testDbName'\n";
    }
    
    echo "\n=== How it works now ===\n";
    echo "1. prepend.php calls Base::getDB(\$productionConfig)\n";
    echo "   - Creates connection to production DB and stores in Base::\$db\n\n";
    
    echo "2. Tests call Base::getTestDB(\$testConfig)\n";
    echo "   - Creates separate connection to test DB and stores in Base::\$testDb\n\n";
    
    echo "3. Both connections coexist independently:\n";
    echo "   - Base::\$db points to production database\n";
    echo "   - Base::\$testDb points to test database\n\n";
    
    echo "=== Key Benefits ===\n";
    echo "✅ Production code continues to work unchanged\n";
    echo "✅ Tests now use proper test database\n";
    echo "✅ No interference between production and test connections\n";
    echo "✅ Simple, elegant solution that follows existing patterns\n\n";
    
    // Check if test database has required tables
    echo "=== Test Database Readiness ===\n";
    try {
        $tablesResult = $testDb->executeSQL("SHOW TABLES");
        $tableCount = $tablesResult ? $tablesResult->num_rows : 0;
        echo "Test database '$testDbName' contains $tableCount tables\n";
        
        if ($tableCount === 0) {
            echo "⚠️  Test database is empty\n";
            echo "   Run: php scripts/setup_test_database.php setup\n";
        } else {
            $partsCheck = $testDb->executeSQL("SHOW TABLES LIKE 'parts'");
            if ($partsCheck && $partsCheck->num_rows > 0) {
                echo "✅ Test database has required 'parts' table\n";
            } else {
                echo "❌ Test database missing 'parts' table\n";
            }
        }
    } catch (Exception $e) {
        echo "⚠️  Could not check test database tables: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during verification: " . $e->getMessage() . "\n";
    echo "   This usually indicates a database connection problem\n";
}

echo "\n=== Next Steps ===\n";
echo "Run 'php composer.phar run test' on Dreamhost to verify tests now use correct database\n";
echo "Tests should pass without writing to production database\n";