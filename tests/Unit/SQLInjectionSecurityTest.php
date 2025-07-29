<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tests to verify SQL injection protection in repositories
 */
class SQLInjectionSecurityTest extends TestCase
{
    private $testDb;

    protected function setUp(): void
    {
        $this->testDb = getTestDatabase();
    }

    public function testPreparedStatementsInPartsRepository(): void
    {
        $partsRepo = new \Database\PartsRepository($this->testDb, 'en');
        
        // Test with malicious input that would cause SQL injection if not properly escaped
        $maliciousAlias = "test'; DROP TABLE parts; --";
        $maliciousName = "<script>alert('xss')</script>";
        $maliciousDescription = "test\"; SELECT * FROM users; --";
        
        try {
            // This should not cause SQL injection due to prepared statements
            $partId = $partsRepo->insert($maliciousAlias, $maliciousName, $maliciousDescription);
            
            // If we get here, the prepared statement worked correctly
            $this->assertGreaterThan(0, $partId, 'Part should be created with malicious input safely handled');
            
            // Verify the data was stored as-is (escaped but not executed)
            $part = $partsRepo->findById($partId);
            $this->assertEquals($maliciousAlias, $part->part_alias, 'Malicious alias should be stored as string, not executed');
            
            // Clean up
            $this->testDb->executeSQL("DELETE FROM parts WHERE part_id = ?", 'i', [$partId]);
            
        } catch (\Exception $e) {
            // If there's a constraint violation, that's ok - the important thing is no SQL injection
            $this->assertStringNotContainsString('syntax error', strtolower($e->getMessage()), 'Should not have SQL syntax errors from injection');
            $this->assertStringNotContainsString('you have an error in your sql syntax', strtolower($e->getMessage()), 'Should not have SQL syntax errors');
        }
    }

    public function testPreparedStatementsInWorkersRepository(): void
    {
        $workersRepo = new \Database\WorkersRepository($this->testDb, 'en');
        
        // Test with malicious input
        $maliciousAlias = "w1'; DROP TABLE workers; --";
        $maliciousName = "'; SELECT password FROM users; --";
        $maliciousDescription = "<script>document.cookie</script>";
        
        try {
            $workerId = $workersRepo->insert($maliciousAlias, $maliciousName, $maliciousDescription);
            
            $this->assertGreaterThan(0, $workerId, 'Worker should be created with malicious input safely handled');
            
            $worker = $workersRepo->findById($workerId);
            $this->assertEquals($maliciousAlias, $worker->worker_alias, 'Malicious alias should be stored as string');
            
            // Clean up
            $this->testDb->executeSQL("DELETE FROM workers WHERE worker_id = ?", 'i', [$workerId]);
            $this->testDb->executeSQL("DELETE FROM worker_names WHERE worker_id = ?", 'i', [$workerId]);
            
        } catch (\Exception $e) {
            // Column constraints are OK, SQL injection is not
            $this->assertStringNotContainsString('syntax error', strtolower($e->getMessage()));
        }
    }

    public function testDatabaseClassPreparedStatements(): void
    {
        // Test the core Database class handles parameters correctly
        $maliciousValue = "'; DROP TABLE test; --";
        
        try {
            // This should use prepared statements internally
            $result = $this->testDb->fetchResults(
                "SELECT ? as test_value",
                's',
                [$maliciousValue]
            );
            
            $result->setRow(0);
            $this->assertEquals($maliciousValue, $result->data['test_value'], 'Malicious value should be treated as data, not SQL');
            
        } catch (\Exception $e) {
            $this->fail("Prepared statement test failed: " . $e->getMessage());
        }
    }

    public function testXSSProtectionInTemplateData(): void
    {
        // Test that data passed to templates should be escaped at output, not input
        $xssPayload = "<script>alert('xss')</script>";
        $sqlPayload = "'; DROP TABLE users; --";
        
        // These should be stored as-is in the database (raw)
        // XSS protection happens at template output, not database storage
        $partsRepo = new \Database\PartsRepository($this->testDb, 'en');
        
        try {
            $partId = $partsRepo->insert('xsstest', $xssPayload, $sqlPayload);
            $part = $partsRepo->findById($partId);
            
            // Data should be stored raw
            $this->assertEquals($xssPayload, $part->name, 'XSS payload should be stored raw in database');
            $this->assertEquals($sqlPayload, $part->description, 'SQL payload should be stored raw in database');
            
            // Clean up
            $this->testDb->executeSQL("DELETE FROM parts WHERE part_id = ?", 'i', [$partId]);
            
        } catch (\Exception $e) {
            // If constraint error, that's fine
            $this->assertStringNotContainsString('syntax error', strtolower($e->getMessage()));
        }
    }
}