<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Integration tests for database security measures
 */
class DatabaseSecurityTest extends TestCase
{
    private $testDb;
    private string $testPrefix;

    protected function setUp(): void
    {
        $this->testDb = getTestDatabase();
        $this->testPrefix = 'sec' . substr(time(), -3) . rand(10, 99) . '_';
    }

    public function testSQLInjectionProtection(): void
    {
        // Test that our database layer properly prevents SQL injection
        $maliciousQueries = [
            "'; DROP TABLE parts; --",
            "' UNION SELECT * FROM users --",
            "'; INSERT INTO users (username, password) VALUES ('hacker', 'password'); --",
            "' OR '1'='1",
            "'; SHOW TABLES; --",
            "' AND (SELECT COUNT(*) FROM users) > 0 --"
        ];

        $partsRepo = new \Database\PartsRepository($this->testDb, 'en');

        foreach ($maliciousQueries as $maliciousInput) {
            try {
                // This should be treated as data, not SQL
                $partId = $partsRepo->insert(
                    $this->testPrefix . 'safe',
                    'Test Part',
                    $maliciousInput // Malicious input as description
                );

                if ($partId > 0) {
                    // If successful, verify the malicious input was stored as data
                    $part = $partsRepo->findById($partId);
                    $this->assertEquals($maliciousInput, $part->description, 'Malicious SQL should be stored as data, not executed');
                    
                    // Clean up
                    $this->testDb->executeSQL("DELETE FROM parts WHERE part_id = ?", 'i', [$partId]);
                }
                
            } catch (\Exception $e) {
                // Constraint violations are OK, SQL syntax errors are not
                $errorMessage = strtolower($e->getMessage());
                $this->assertStringNotContainsString('syntax error', $errorMessage, 'Should not have SQL syntax errors from injection');
                $this->assertStringNotContainsString('you have an error in your sql syntax', $errorMessage, 'Should not have SQL syntax errors');
            }
        }
    }

    public function testParameterizedQuerySafety(): void
    {
        // Test that our parameterized queries work correctly with edge cases
        $edgeCases = [
            '', // empty string
            null, // null value
            '0', // string zero
            0, // integer zero
            'normal text',
            "text with 'single quotes'",
            'text with "double quotes"',
            "text with\nnewlines",
            "text with\ttabs",
            "text with unicode: こんにちは",
            "text with emoji: 🔒",
            str_repeat('a', 1000), // long string
        ];

        $partsRepo = new \Database\PartsRepository($this->testDb, 'en');

        foreach ($edgeCases as $index => $testValue) {
            try {
                $partId = $partsRepo->insert(
                    $this->testPrefix . $index,
                    'Edge Case Test',
                    (string)$testValue
                );

                if ($partId > 0) {
                    $part = $partsRepo->findById($partId);
                    $this->assertEquals((string)$testValue, $part->description, "Edge case value should be stored correctly");
                    
                    // Clean up
                    $this->testDb->executeSQL("DELETE FROM parts WHERE part_id = ?", 'i', [$partId]);
                }
                
            } catch (\Exception $e) {
                // Some edge cases might fail due to constraints, but not due to SQL errors
                $this->assertStringNotContainsString('syntax error', strtolower($e->getMessage()));
            }
        }
    }

    public function testDatabaseErrorHandling(): void
    {
        // Test that database errors don't leak sensitive information
        $partsRepo = new \Database\PartsRepository($this->testDb, 'en');

        try {
            // Try to create a part with duplicate alias (should fail due to unique constraint)
            $partId1 = $partsRepo->insert($this->testPrefix . 'dup', 'First Part', 'Description 1');
            $partId2 = $partsRepo->insert($this->testPrefix . 'dup', 'Second Part', 'Description 2'); // Should fail
            
            // If we get here, the second insert should have failed
            $this->fail('Duplicate alias should have caused a constraint violation');
            
        } catch (\Exception $e) {
            // Error message should be about constraint violation, not expose internal details
            $errorMessage = $e->getMessage();
            
            // Should mention the constraint issue
            $this->assertStringContainsString('Duplicate', $errorMessage, 'Error should mention duplicate entry');
            
            // Should not expose sensitive paths or internal structure
            $this->assertStringNotContainsString('/var/www', $errorMessage, 'Should not expose system paths');
            $this->assertStringNotContainsString('password', strtolower($errorMessage), 'Should not expose password info');
            
            // Clean up
            if (isset($partId1)) {
                $this->testDb->executeSQL("DELETE FROM parts WHERE part_id = ?", 'i', [$partId1]);
            }
        }
    }

    public function testTransactionSafety(): void
    {
        // Test that transactions work correctly and can't be bypassed
        try {
            // Start a transaction
            $this->testDb->beginTransaction();
            
            $partsRepo = new \Database\PartsRepository($this->testDb, 'en');
            $partId = $partsRepo->insert($this->testPrefix . 'trans', 'Transaction Test', 'Description');
            
            // Verify part exists within transaction
            $part = $partsRepo->findById($partId);
            $this->assertNotNull($part, 'Part should exist within transaction');
            
            // Rollback the transaction
            $this->testDb->rollback();
            
            // Verify part no longer exists after rollback
            $partAfterRollback = $partsRepo->findById($partId);
            $this->assertNull($partAfterRollback, 'Part should not exist after rollback');
            
        } catch (\Exception $e) {
            // If transaction failed, make sure we clean up
            try {
                $this->testDb->rollback();
            } catch (\Exception $rollbackException) {
                // Ignore rollback errors
            }
            throw $e;
        }
    }

    protected function tearDown(): void
    {
        // Clean up any test data that might have been left behind
        try {
            $this->testDb->executeSQL("DELETE FROM parts WHERE part_alias LIKE ?", 's', [$this->testPrefix . '%']);
            $this->testDb->executeSQL("DELETE FROM part_translations WHERE part_id IN (SELECT part_id FROM parts WHERE part_alias LIKE ?)", 's', [$this->testPrefix . '%']);
        } catch (\Exception $e) {
            // Ignore cleanup errors
        }
    }
}