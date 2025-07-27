# Testing Strategy for MarbleTrack3

## The Database Testing Challenge

The recent moment-saving regression highlighted a critical issue: **database-dependent bugs can only be caught by tests that interact with a database that matches the production schema**. This document explores different approaches to testing, their trade-offs, and practical recommendations.

## The Core Problem

Our recent bug was a SQL parameter mismatch:
```php
// This failed because parameter count didn't match placeholders
"UPDATE moments SET notes = ?, frame_start = ?, frame_end = ?, phrase_id = ?, take_id = ?, moment_date = ? WHERE moment_id = ?",
'siiiisi',  // 7 type characters
[$notes, $frame_start, $frame_end, $take_id, $moment_date, $moment_id]  // 6 parameters
```

**Unit tests alone cannot catch this** because they don't execute real SQL against real tables. We need integration testing with actual database interactions.

## Testing Approaches: Pros and Cons

### 1. Testing Against Production Database

**Pros:**
- Guaranteed schema accuracy
- Catches real-world data issues
- No setup/maintenance overhead
- Tests against actual data constraints

**Cons:**
- **DANGEROUS** - can corrupt live data
- Tests affect real users
- Cannot test destructive operations
- Parallel test runs interfere with each other
- Ethical and professional concerns

**Verdict:** ❌ **Never recommended for automated testing**

### 2. Separate Test Database (Manual Schema Sync)

**Pros:**
- Safe isolation from production
- Real database interactions
- Can test destructive operations
- Full SQL validation

**Cons:**
- **Schema drift** - test DB becomes outdated
- Manual maintenance burden
- Developers forget to update test schema
- False negatives when schemas diverge
- Time-consuming setup

**Implementation Example (Dreamhost Shared Hosting):**
```bash
# Manual sync process using existing DBPersistaroo backup capability
# 1. Create backup of production via existing backup system
php -r "
$config = new Config();
$persistaroo = new Database\DBPersistaroo($config);
$persistaroo->ensureBackupIsRecent();
"

# 2. Get the latest backup file
LATEST_BACKUP=$(ls -t db_backups/*.sql | head -1)

# 3. Import to test database (created via Dreamhost panel)
mysql -h mysql.marbletrack3.com -u username_test -p db_marbletrack3_test < "$LATEST_BACKUP"
```

**Verdict:** 🟡 **Workable but maintenance-heavy** ⭐ **Most realistic option for Dreamhost**

### 3. Automated Test Database Provisioning

**Pros:**
- Always up-to-date schema
- Automated consistency
- Fresh, clean state for each test run
- Can run migrations automatically
- Parallel testing possible

**Cons:**
- Complex CI/CD setup required
- Slower test execution
- Infrastructure overhead
- **MAJOR LIMITATION: Requires root/admin access for database creation**
- **Not possible on Dreamhost shared hosting**

**Implementation Example:**
```yaml
# GitHub Actions example - NOT APPLICABLE for Dreamhost shared hosting
- name: Setup Test Database
  run: |
    # This requires root MySQL access - not available on shared hosting
    mysql -e "DROP DATABASE IF EXISTS marbletrack3_test;"
    mysql -e "CREATE DATABASE marbletrack3_test;"

    # Copy schema from production (without data)
    mysqldump marbletrack3_prod --no-data | mysql marbletrack3_test

    # Run any pending migrations
    php run_migrations.php --env=test
```

**Verdict:** ❌ **Not possible on Dreamhost shared hosting** (requires root access)

### 4. Database Mocking/Stubbing

**Pros:**
- Fast execution
- No database dependencies
- Predictable behavior
- Easy to run anywhere

**Cons:**
- **Cannot catch SQL syntax errors**
- Doesn't validate actual database constraints
- Mocks can become outdated
- False sense of security

**Example:**
```php
// This would pass with mocks but fail with real DB
$mockDB->expects($this->once())
       ->method('executeSQL')
       ->with('UPDATE moments SET...', 'siiiisi', $params)
       ->willReturn(true);  // Mock always succeeds
```

**Verdict:** 🟡 **Useful for unit tests, insufficient alone**

### 5. Schema-in-Code Approach

**Pros:**
- Version controlled schema
- Automated migrations
- Consistent across environments
- Easy to recreate test databases

**Cons:**
- Requires disciplined migration practices
- Initial setup effort
- Must maintain migration scripts
- Schema changes need migration files

**Implementation:**
```php
// Migration-based approach
class CreateMomentsTable extends Migration
{
    public function up() {
        $this->db->executeSQL("
            CREATE TABLE moments (
                moment_id INT AUTO_INCREMENT PRIMARY KEY,
                notes TEXT,
                frame_start INT,
                frame_end INT,
                take_id INT,
                moment_date DATE
            )
        ");
    }
}
```

**Verdict:** ✅ **Recommended long-term approach**

## Dreamhost Shared Hosting Constraints

### What We CAN Do:
- ✅ Create additional databases via Dreamhost panel
- ✅ Use `mysqldump` to backup/copy data
- ✅ Run PHP scripts for testing
- ✅ Execute MySQL queries through PHP
- ✅ Use existing DBPersistaroo backup system
- ✅ SSH access for running scripts and installing composer packages
- ✅ Install linting tools (phpcs, phpstan) in user directory

### What We CANNOT Do:
- ❌ Create databases programmatically (no `CREATE DATABASE` privileges)
- ❌ Drop databases programmatically (no `DROP DATABASE` privileges)
- ❌ Install system packages (no root access)
- ❌ Run Docker containers
- ❌ Install custom MySQL instances
- ❌ Modify MySQL configuration

### Practical Dreamhost Testing Strategy

Given these constraints, the most viable approach is:

1. **Manual Test Database Setup** (one-time)
   - Create `marbletrack3_test` database via Dreamhost panel
   - Set up separate database user with appropriate permissions

2. **Schema Sync Using Existing Backup System**
   - Leverage your existing `DBPersistaroo` class
   - Create helper script to sync test database

3. **Local Development Testing**
   - Run comprehensive tests locally with full MySQL control
   - Use CI/CD for additional validation if desired

## Hybrid Testing Strategy (Dreamhost-Adapted)

### Level 1: Unit Tests (Fast, Many) ✅ **Fully Compatible**
- Test business logic in isolation
- Mock database interactions
- Catch obvious bugs quickly
- Run on every commit (locally or CI/CD)

### Level 2: Integration Tests (Medium speed, Some) ⚠️ **Limited**
- Test against manually-created test database
- Sync schema using backup system
- Validate SQL syntax and constraints
- Run manually or via scheduled scripts

### Level 3: End-to-End Tests (Slow, Few) ✅ **Compatible**
- Test complete user journeys
- Browser automation against staging/test environment
- Can run locally or via CI/CD

### Level 4: Smoke Tests (Production) ✅ **Compatible**
- Minimal tests against production
- Health checks only
- Monitor critical paths
- Alert on failures

### Level 1: Unit Tests (Fast, Many)
- Test business logic in isolation
- Mock database interactions
- Catch obvious bugs quickly
- Run on every commit

### Level 2: Integration Tests (Medium speed, Some)
- Test against real test database
- Validate SQL syntax and constraints
- Test complete workflows
- Run on pull requests

### Level 3: End-to-End Tests (Slow, Few)
- Test complete user journeys
- Browser automation (Playwright/Cypress)
- Run on releases/deployments

### Level 4: Smoke Tests (Production)
- Minimal tests against production
- Health checks only
- Monitor critical paths
- Alert on failures

## Practical Implementation Plan (Dreamhost-Adapted)

### Phase 1: Quick Wins (1-2 weeks)
1. **Create test database manually** via Dreamhost panel (`marbletrack3_test`)
2. **Create database sync script** using existing DBPersistaroo
3. **Add basic integration tests** for critical paths
4. **Implement SQL validation tests** for common queries
5. **Add form submission tests** to catch parameter mismatches

### Phase 2: Systematic Testing (1-2 months)
1. **Implement migration tracking system** (file-based, since we can't auto-create DBs)
2. **Create comprehensive integration test suite**
3. **Set up local testing environment** with full MySQL control
4. **Implement database fixtures** for consistent test data
5. **Add scheduled test runs** via cron jobs

### Phase 3: Advanced Testing (3-6 months)
1. **Full end-to-end test suite** (can run locally or CI/CD)
2. **Performance and load testing**
3. **Database constraint testing**
4. **Cross-browser compatibility testing**
5. **Consider upgrading to VPS** if testing needs outgrow shared hosting

## Dreamhost-Specific Test Database Setup

### One-Time Setup (via Dreamhost Panel)
1. Log into Dreamhost panel
2. Go to "MySQL Databases"
3. Create new database: `marbletrack3_test`
4. Create new user: `mt3_test_user`
5. Grant all privileges on `marbletrack3_test` to `mt3_test_user`

### Database Sync Script (Leveraging DBPersistaroo)
```php
<?php
// scripts/sync_test_database.php

require_once __DIR__ . '/../prepend.php';

class TestDatabaseSyncer
{
    private \Config $config;
    private string $testDbName = 'marbletrack3_test';
    private string $testDbUser = 'mt3_test_user';
    private string $testDbPass = 'test_password';

    public function __construct(\Config $config)
    {
        $this->config = $config;
    }

    public function syncTestDatabase(): void
    {
        echo "Starting test database sync...\n";

        // 1. Create fresh backup using existing system
        $persistaroo = new \Database\DBPersistaroo($this->config);
        $persistaroo->ensureBackupIsRecent();
        echo "✓ Production backup created\n";

        // 2. Find latest backup file
        $backupDir = $this->config->app_path . '/db_backups';
        $backups = glob($backupDir . '/*.sql');
        if (empty($backups)) {
            throw new Exception("No backup files found!");
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
    }

    private function clearTestDatabase(): void
    {
        $testDb = $this->getTestDatabaseConnection();

        // Get all tables
        $result = $testDb->executeSQL("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch()) {
            $tables[] = $row[0];
        }

        // Disable foreign key checks and truncate all tables
        $testDb->executeSQL("SET FOREIGN_KEY_CHECKS = 0");
        foreach ($tables as $table) {
            $testDb->executeSQL("TRUNCATE TABLE `{$table}`");
        }
        $testDb->executeSQL("SET FOREIGN_KEY_CHECKS = 1");
    }

    private function importBackupToTest(string $backupFile): void
    {
        $host = escapeshellarg($this->config->dbHost);
        $user = escapeshellarg($this->testDbUser);
        $pass = escapeshellarg($this->testDbPass);
        $dbName = escapeshellarg($this->testDbName);
        $backupFile = escapeshellarg($backupFile);

        $command = "mysql -h {$host} -u {$user} -p{$pass} {$dbName} < {$backupFile}";
        $output = shell_exec($command . " 2>&1");

        if ($output) {
            echo "Import output: " . $output . "\n";
        }
    }

    private function sanitizeTestData(): void
    {
        $testDb = $this->getTestDatabaseConnection();

        // Remove or anonymize sensitive data
        // Example: Clear user passwords, email addresses, etc.
        $testDb->executeSQL("UPDATE users SET password_hash = 'test_hash'");

        // Add other sanitization as needed...
    }

    private function getTestDatabaseConnection(): \Database\Database
    {
        // Create test database connection
        $testConfig = clone $this->config;
        $testConfig->dbName = $this->testDbName;
        $testConfig->dbUser = $this->testDbUser;
        $testConfig->dbPass = $this->testDbPass;

        return \Database\Base::getDB($testConfig);
    }
}

// Run the sync
try {
    $syncer = new TestDatabaseSyncer(new \Config());
    $syncer->syncTestDatabase();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

### Automated Sync via Cron (Optional)
```bash
# Add to crontab to sync test DB weekly
0 2 * * 1 cd /home/thunderrabbit/work/rob/db.marbletrack3.com && php scripts/sync_test_database.php
```

### SQL Parameter Validation Test (Dreamhost-Compatible)
```php
class MomentDatabaseTest extends TestCase
{
    private \Database\Database $testDb;

    protected function setUp(): void
    {
        // Connect to test database (created manually via Dreamhost panel)
        $testConfig = new \Config();
        $testConfig->dbName = 'marbletrack3_test';
        $testConfig->dbUser = 'mt3_test_user';
        $testConfig->dbPass = 'test_password';

        $this->testDb = \Database\Base::getDB($testConfig);
    }

    public function testMomentUpdateParameterCount()
    {
        $sql = "UPDATE moments SET notes = ?, frame_start = ?, frame_end = ?, take_id = ?, moment_date = ? WHERE moment_id = ?";
        $params = ['test', 100, 200, 1, '2024-01-01', 1];
        $types = 'siiisi';

        // This would have caught our bug
        $this->assertEquals(substr_count($sql, '?'), count($params));
        $this->assertEquals(substr_count($sql, '?'), strlen($types));

        // Actually execute against test DB
        $result = $this->testDb->executeSQL($sql, $types, $params);
        $this->assertTrue($result);
    }
}
```

### Form Integration Test (Dreamhost-Compatible)
```php
public function testMomentFormSubmission()
{
    // Set up test data in our manually created test database
    $testTakeId = $this->createTestTake();

    // Simulate form submission
    $_POST = [
        'notes' => 'Test moment',
        'frame_start' => '100',
        'frame_end' => '200',
        'take_id' => $testTakeId,
        'moment_date' => '2024-01-01'
    ];

    // Test the form handler with test database
    $momentRepo = new \Database\MomentRepository($this->testDb);
    $result = $momentRepo->insert(100, 200, $testTakeId, 'Test moment', '2024-01-01');

    $this->assertGreaterThan(0, $result);

    // Verify data was actually saved
    $savedMoment = $this->testDb->executeSQL(
        "SELECT * FROM moments WHERE notes = ?",
        's',
        ['Test moment']
    );
    $this->assertCount(1, $savedMoment->toArray());
}

private function createTestTake(): int
{
    $this->testDb->executeSQL(
        "INSERT INTO takes (take_name) VALUES (?)",
        's',
        ['Test Take for Integration Test']
    );
    return $this->testDb->insertId();
}
```

## Database Setup Scripts

### Test Database Creator
```php
<?php
// scripts/setup_test_db.php
class TestDatabaseSetup
{
    public function createTestDatabase()
    {
        // Drop and recreate test database
        $this->executeCommand("mysql -e 'DROP DATABASE IF EXISTS marbletrack3_test;'");
        $this->executeCommand("mysql -e 'CREATE DATABASE marbletrack3_test;'");

        // Copy schema from production (structure only)
        $this->executeCommand("mysqldump marbletrack3_prod --no-data --routines --triggers | mysql marbletrack3_test");

        // Insert minimal test data
        $this->seedTestData();

        echo "Test database created successfully\n";
    }

    private function seedTestData()
    {
        // Add minimal data needed for tests
        $testDb = Database\Base::getTestDB();
        $testDb->executeSQL("INSERT INTO takes (take_name) VALUES ('Test Take 1')");
        // Add other essential test data...
    }
}
```

## CI/CD Integration (Local/External Only)

**Note:** Due to Dreamhost shared hosting limitations, automated CI/CD with database testing must run externally (GitHub Actions, local development, etc.) or use alternative approaches.

### GitHub Actions Example (External Testing)
```yaml
name: Run Tests
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: marbletrack3_test
        ports:
          - 3306:3306

    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'

      - name: Install dependencies
        run: composer install

      - name: Setup test database schema
        run: |
          # Since we can't directly access Dreamhost production,
          # maintain a schema.sql file in version control
          mysql -h127.0.0.1 -uroot -proot marbletrack3_test < tests/schema/production_schema.sql

      - name: Run unit tests
        run: vendor/bin/phpunit tests/Unit

      - name: Run integration tests (external DB)
        run: vendor/bin/phpunit tests/Integration

      - name: Run browser tests
        run: vendor/bin/phpunit tests/Browser
```

### Local Development Testing
```bash
# Local development setup (with full MySQL control)
docker run --name marbletrack3-test-mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=marbletrack3_test -p 3306:3306 -d mysql:8.0

# Import schema from Dreamhost backup
mysql -h127.0.0.1 -uroot -proot marbletrack3_test < latest_dreamhost_backup.sql

# Run tests locally
vendor/bin/phpunit
```

## Maintenance Considerations

### Schema Change Workflow
1. **Create migration file** for any schema changes
2. **Run migration on test database**
3. **Update affected tests**
4. **Run full test suite**
5. **Deploy migration to production**

### Regular Maintenance Tasks
- **Weekly:** Verify test database schema matches production
- **Monthly:** Review and update test fixtures
- **Quarterly:** Audit test coverage and add missing scenarios

## Conclusion

The database testing problem is real and significant, but solvable with the right approach. The key is accepting that:

1. **Some bugs require database testing to catch**
2. **Test database maintenance is a necessary investment**
3. **Automation is essential for sustainability**
4. **A layered testing approach is most effective**

The upfront effort to establish proper database testing will pay dividends by catching regressions like our recent parameter mismatch before they reach production.

The most pragmatic approach is to start with manual test database setup and basic integration tests, then gradually automate the provisioning and expand coverage over time.

## Current Testing Status (2025)

### ✅ **Implemented Solutions:**
1. **Test database setup** - `dbmt3_test` created and functional
2. **Comprehensive test runner** - `scripts/run_all_tests.php` covering 5 test categories:
   - Form Field Validation (Issue #57/58 prevention)
   - SQL Parameter Validation 
   - AJAX Endpoint Security
   - Database Connectivity
   - Regression prevention tests
3. **RobRequest class** - Eliminates AJAX security warnings, reduces boilerplate
4. **DBPersistaroo integration** - Automated production data sync to test database

### 📊 **Current Test Results:**
```
🏁 Test Suite Complete
Tests run: 5
Passed: 5
Failed: 0
Duration: 2349ms

🎉 All tests passed! Your codebase is looking good.
```

### 🔄 **Regular Testing Workflow:**
1. `php scripts/setup_test_database.php sync` - Refresh test data (as needed)
2. `php scripts/run_all_tests.php` - Run full validation suite
3. Fix any warnings or failures before deployment

## Summary: Best Approach for Dreamhost Shared Hosting

Given the constraints of Dreamhost shared hosting, the most practical testing strategy is:

### ✅ **Current Implementation:**
1. **Manual test database setup** via Dreamhost panel (`dbmt3_test`) ✅ **DONE**
2. **DBPersistaroo-based sync** for production data to test database ✅ **DONE**
3. **Comprehensive validation tests** catching SQL parameter mismatches ✅ **DONE**
4. **AJAX security validation** with RobRequest class ✅ **DONE**
5. **Form field validation** preventing Issue #57/58 regressions ✅ **DONE**

### ⚠️ **Accepted Limitations:**
- Cannot auto-create/drop databases (must use Dreamhost panel)
- Cannot run tests directly on production server in automated fashion
- Test database sync requires manual process or scheduled scripts
- Full automation requires external infrastructure

### 🎯 **This Implementation Catches Your Bugs:**
The current test suite successfully catches:
- SQL parameter mismatches that broke moment saving
- Form field mapping issues (Issue #57/58)
- AJAX endpoint security concerns
- Database connectivity problems

### 📋 **TODO: Code Quality Improvements**
- **PHP Linting Integration**: Add PHP_CodeSniffer or PHPStan 
  - Install on Dreamhost via SSH: `composer global require squizlabs/php_codesniffer`
  - Run on server: `phpcs --standard=PSR12 classes/`
  - Could integrate into existing test runner as 6th validation category
- **Performance Testing**: Add basic performance regression tests
- **Browser Testing**: Consider Playwright/Cypress for end-to-end testing

The key insight is that while Dreamhost shared hosting limits our automation options, we can still achieve effective testing by:
- Using manual setup where automation isn't possible
- Leveraging existing backup systems for data sync
- Running comprehensive tests in external environments
- Focusing on the tests that matter most for catching real bugs

This pragmatic approach balances the reality of shared hosting constraints with the need for effective regression testing.
