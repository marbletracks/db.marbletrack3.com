<?php
/**
 * Test Database Setup Script
 * This script helps set up and sync the test database using the existing DBPersistaroo backup system
 * Based on the strategy outlined in TESTING.md
 */

require_once __DIR__ . '/../prepend.php';

class TestDatabaseSetup
{
    private \Config $config;
    private \Database\Database $prodDb;
    private string $testDbName;
    
    public function __construct()
    {
        $this->config = new \Config();
        $this->prodDb = \Database\Base::getDB($this->config);
        $this->testDbName = $this->config->dbName . '_test';
    }
    
    /**
     * Check if test database exists and is accessible
     */
    public function checkTestDatabaseAccess(): array
    {
        $errors = [];
        
        try {
            $testConfig = new TestConfig();
            $testDb = \Database\Base::getDB($testConfig);
            
            // Try a simple query to test connectivity
            $result = $testDb->executeSQL("SELECT 1 as test");
            if ($result) {
                echo "✓ Test database connection successful\n";
            } else {
                $errors[] = "Could not execute test query on test database";
            }
            
        } catch (\Exception $e) {
            $errors[] = "Test database connection failed: " . $e->getMessage();
            echo "❌ Test database connection failed\n";
            echo "Error: " . $e->getMessage() . "\n";
            echo "\nTo set up the test database:\n";
            echo "1. Log into Dreamhost panel\n";
            echo "2. Go to 'MySQL Databases'\n";
            echo "3. Create new database: '{$this->testDbName}'\n";
            echo "4. Create new user or grant access to existing user\n";
            echo "5. Update your Config.php with test database credentials\n";
        }
        
        return $errors;
    }
    
    /**
     * Sync test database with production using existing backup system
     * Based on TESTING.md recommendations
     */
    public function syncTestDatabase(): void
    {
        echo "Starting test database sync...\n";
        
        try {
            // 1. Create fresh backup using existing system
            $persistaroo = new \Database\DBPersistaroo($this->config);
            $persistaroo->ensureBackupIsRecent();
            echo "✓ Production backup created\n";
            
            // 2. Find latest backup file
            $backupDir = $this->config->app_path . '/db_backups';
            if (!is_dir($backupDir)) {
                throw new \Exception("Backup directory not found: {$backupDir}");
            }
            
            $backups = glob($backupDir . '/*.sql');
            if (empty($backups)) {
                throw new \Exception("No backup files found in {$backupDir}");
            }
            
            // Get most recent backup
            usort($backups, fn($a, $b) => filemtime($b) - filemtime($a));
            $latestBackup = $backups[0];
            echo "✓ Using backup: " . basename($latestBackup) . "\n";
            
            // 3. Clear test database (truncate all tables since we can't DROP/CREATE)
            $this->clearTestDatabase();
            echo "✓ Test database cleared\n";
            
            // 4. Import backup to test database
            $this->importBackupToTest($latestBackup);
            echo "✓ Backup imported to test database\n";
            
            // 5. Clean up test data (remove sensitive production data)
            $this->sanitizeTestData();
            echo "✓ Test data sanitized\n";
            
            echo "Test database sync complete!\n";
            
        } catch (\Exception $e) {
            echo "❌ Test database sync failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    /**
     * Clear test database by truncating all tables
     * (We can't DROP/CREATE database on shared hosting)
     */
    private function clearTestDatabase(): void
    {
        $testConfig = new TestConfig();
        $testDb = \Database\Base::getDB($testConfig);
        
        // Get all tables
        $result = $testDb->executeSQL("SHOW TABLES");
        $tables = [];
        
        while ($row = $result->fetch()) {
            $tables[] = $row[0];
        }
        
        if (empty($tables)) {
            echo "No tables found in test database - this might be the first run\n";
            return;
        }
        
        // Disable foreign key checks and truncate all tables
        $testDb->executeSQL("SET FOREIGN_KEY_CHECKS = 0");
        foreach ($tables as $table) {
            try {
                $testDb->executeSQL("TRUNCATE TABLE `{$table}`");
            } catch (\Exception $e) {
                echo "Warning: Could not truncate table {$table}: " . $e->getMessage() . "\n";
            }
        }
        $testDb->executeSQL("SET FOREIGN_KEY_CHECKS = 1");
    }
    
    /**
     * Import backup to test database using mysql command
     */
    private function importBackupToTest(string $backupFile): void
    {
        $testConfig = new TestConfig();
        
        // Build mysql command for import
        $host = escapeshellarg($testConfig->dbHost);
        $user = escapeshellarg($testConfig->dbUser);
        $pass = escapeshellarg($testConfig->dbPass);
        $dbName = escapeshellarg($testConfig->dbName);
        $backupFile = escapeshellarg($backupFile);
        
        $command = "mysql -h {$host} -u {$user} -p{$pass} {$dbName} < {$backupFile}";
        
        // Execute the import command
        $output = shell_exec($command . " 2>&1");
        
        if ($output) {
            echo "Import output: " . $output . "\n";
            
            // Check if there were any errors
            if (strpos(strtolower($output), 'error') !== false) {
                throw new \Exception("MySQL import reported errors: " . $output);
            }
        }
    }
    
    /**
     * Remove or anonymize sensitive data in test database
     */
    private function sanitizeTestData(): void
    {
        $testConfig = new TestConfig();
        $testDb = \Database\Base::getDB($testConfig);
        
        try {
            // Remove or anonymize sensitive data if tables exist
            // Example: Clear user passwords, email addresses, etc.
            $testDb->executeSQL("UPDATE users SET password_hash = 'test_hash' WHERE password_hash IS NOT NULL");
            echo "✓ User passwords sanitized\n";
            
        } catch (\Exception $e) {
            // Tables might not exist yet, that's okay
            echo "Note: Some sanitization skipped (tables may not exist): " . $e->getMessage() . "\n";
        }
        
        // Add test data markers
        try {
            $testDb->executeSQL("INSERT INTO parts (alias, name, description) VALUES ('test_marker', 'Test Data Marker', 'This part indicates test database is properly set up') ON DUPLICATE KEY UPDATE description = VALUES(description)");
            echo "✓ Test data marker added\n";
        } catch (\Exception $e) {
            echo "Note: Could not add test marker: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Validate that test database is working properly
     */
    public function validateTestDatabase(): bool
    {
        try {
            $testConfig = new TestConfig();
            $testDb = \Database\Base::getDB($testConfig);
            
            // Test basic operations
            $result = $testDb->executeSQL("SELECT COUNT(*) as count FROM parts");
            $row = $result->fetch();
            $partCount = $row['count'] ?? 0;
            
            echo "✓ Test database contains {$partCount} parts\n";
            
            // Test if our test marker exists
            $markerResult = $testDb->executeSQL("SELECT * FROM parts WHERE alias = 'test_marker'");
            if ($markerResult && $markerResult->fetch()) {
                echo "✓ Test data marker found\n";
                return true;
            } else {
                echo "⚠ Test data marker not found - database may not be properly synced\n";
                return false;
            }
            
        } catch (\Exception $e) {
            echo "❌ Test database validation failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $setup = new TestDatabaseSetup();
    
    $command = $argv[1] ?? 'help';
    
    switch ($command) {
        case 'check':
            echo "Checking test database access...\n";
            $errors = $setup->checkTestDatabaseAccess();
            if (empty($errors)) {
                echo "✓ Test database access OK\n";
            } else {
                echo "❌ Test database access problems:\n";
                foreach ($errors as $error) {
                    echo "  - {$error}\n";
                }
                exit(1);
            }
            break;
            
        case 'sync':
            echo "Syncing test database with production...\n";
            $setup->syncTestDatabase();
            break;
            
        case 'validate':
            echo "Validating test database...\n";
            $isValid = $setup->validateTestDatabase();
            exit($isValid ? 0 : 1);
            
        case 'setup':
            echo "Full test database setup...\n";
            $errors = $setup->checkTestDatabaseAccess();
            if (!empty($errors)) {
                echo "❌ Cannot proceed with setup due to access issues\n";
                exit(1);
            }
            $setup->syncTestDatabase();
            $isValid = $setup->validateTestDatabase();
            echo $isValid ? "✓ Test database setup complete\n" : "❌ Test database setup failed validation\n";
            exit($isValid ? 0 : 1);
            
        default:
            echo "Test Database Setup Script\n";
            echo "Usage: php " . basename(__FILE__) . " <command>\n";
            echo "\nCommands:\n";
            echo "  check    - Check if test database is accessible\n";
            echo "  sync     - Sync test database with production backup\n";
            echo "  validate - Validate test database is working\n";
            echo "  setup    - Full setup (check + sync + validate)\n";
            echo "\nBased on strategy from TESTING.md\n";
            break;
    }
}