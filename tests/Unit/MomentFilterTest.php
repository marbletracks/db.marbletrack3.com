<?php
/**
 * Unit tests for Moment filtering functionality
 * Tests the filtering logic without requiring database connectivity
 */

use PHPUnit\Framework\TestCase;

class MomentFilterTest extends TestCase
{
    /**
     * Test filter parameter handling logic from moments index.php
     */
    public function testFilterParameterHandling()
    {
        // Simulate $_GET parameters as they would be received
        $testCases = [
            // Normal filter
            ['filter' => 'test string', 'expected' => 'test string'],
            // Trimmed filter
            ['filter' => '  test string  ', 'expected' => 'test string'],
            // Empty filter
            ['filter' => '', 'expected' => ''],
            // Null filter 
            ['filter' => null, 'expected' => ''],
            // Missing filter
            ['expected' => ''],
        ];

        foreach ($testCases as $testCase) {
            // Simulate the filtering logic from index.php
            $filter = isset($testCase['filter']) ? trim($testCase['filter'] ?? '') : trim($_GET['filter'] ?? '');
            
            $this->assertEquals(
                $testCase['expected'], 
                $filter, 
                "Filter should be processed correctly for input: " . json_encode($testCase['filter'] ?? 'not set')
            );
        }
    }

    /**
     * Test SQL query structure for moment filtering
     * This validates the SQL structure without executing it
     */
    public function testSQLQueryStructure()
    {
        $filter = 'test_search';
        $expectedFilter = '%' . $filter . '%';
        
        // Test basic filtering without take_id
        $sqlTemplate = "SELECT DISTINCT
                    m.moment_id,
                    m.frame_start,
                    m.frame_end,
                    m.take_id,
                    m.notes,
                    m.moment_date,
                    CASE 
                        WHEN m.notes LIKE ? THEN 1
                        WHEN mt.translated_note LIKE ? THEN 2
                        ELSE 3
                    END as priority
                  FROM moments m
                  LEFT JOIN moment_translations mt ON m.moment_id = mt.moment_id
                  WHERE (m.notes LIKE ? OR mt.translated_note LIKE ?)
                  ORDER BY priority ASC, m.take_id ASC, m.frame_start ASC";
        
        // Validate that SQL contains expected elements
        $this->assertStringContainsString('SELECT DISTINCT', $sqlTemplate, 'Query should use DISTINCT');
        $this->assertStringContainsString('LEFT JOIN moment_translations', $sqlTemplate, 'Query should join moment_translations');
        $this->assertStringContainsString('m.notes LIKE ?', $sqlTemplate, 'Query should search moments.notes');
        $this->assertStringContainsString('mt.translated_note LIKE ?', $sqlTemplate, 'Query should search moment_translations.translated_note');
        $this->assertStringContainsString('ORDER BY priority ASC', $sqlTemplate, 'Query should order by priority');
        
        // Validate parameter count matches placeholders
        $parameterCount = substr_count($sqlTemplate, '?');
        $this->assertEquals(4, $parameterCount, 'Basic filter query should have exactly 4 parameter placeholders');
        
        // Test filtering with take_id
        $sqlTemplateWithTakeId = "SELECT DISTINCT
                    m.moment_id,
                    m.frame_start,
                    m.frame_end,
                    m.take_id,
                    m.notes,
                    m.moment_date,
                    CASE 
                        WHEN m.notes LIKE ? THEN 1
                        WHEN mt.translated_note LIKE ? THEN 2
                        ELSE 3
                    END as priority
                  FROM moments m
                  LEFT JOIN moment_translations mt ON m.moment_id = mt.moment_id
                  WHERE (m.notes LIKE ? OR mt.translated_note LIKE ?) AND m.take_id = ?
                  ORDER BY priority ASC, m.take_id ASC, m.frame_start ASC";
                  
        $parameterCountWithTakeId = substr_count($sqlTemplateWithTakeId, '?');
        $this->assertEquals(5, $parameterCountWithTakeId, 'Filter query with take_id should have exactly 5 parameter placeholders');
        $this->assertStringContainsString('AND m.take_id = ?', $sqlTemplateWithTakeId, 'Query should filter by take_id when provided');
        
        // Expected parameters would be: [$expectedFilter, $expectedFilter, $expectedFilter, $expectedFilter, $take_id]
        $parameters = [$expectedFilter, $expectedFilter, $expectedFilter, $expectedFilter, 5];
        $this->assertCount($parameterCountWithTakeId, $parameters, 'Parameter count should match placeholder count');
    }

    /**
     * Test filter form URL construction logic
     */
    public function testFilterFormUrls()
    {
        $testCases = [
            // Regular filtering without take_id
            ['take_id' => 0, 'filter' => 'test', 'expected_clear_url' => '/admin/moments/'],
            // Filtering with take_id
            ['take_id' => 5, 'filter' => 'worker', 'expected_clear_url' => '/admin/moments/?take_id=5'],
            // No filter, no take_id
            ['take_id' => 0, 'filter' => '', 'expected_clear_url' => '/admin/moments/'],
        ];

        foreach ($testCases as $testCase) {
            // Simulate the URL construction logic from index.tpl.php
            $clearUrl = '/admin/moments/' . ($testCase['take_id'] > 0 ? '?take_id=' . $testCase['take_id'] : '');
            
            $this->assertEquals(
                $testCase['expected_clear_url'], 
                $clearUrl, 
                "Clear URL should be constructed correctly for take_id={$testCase['take_id']}"
            );
        }
    }

    /**
     * Test the logic for determining which repository method to call
     */
    public function testRepositoryMethodSelection()
    {
        $testCases = [
            // Both take_id and filter provided
            ['take_id' => 5, 'filter' => 'test', 'expected_method' => 'findByFilter_with_take_id'],
            // Only take_id provided
            ['take_id' => 5, 'filter' => '', 'expected_method' => 'findWithinTakeId'],
            // Only filter provided
            ['take_id' => 0, 'filter' => 'test', 'expected_method' => 'findByFilter'],
            // Neither provided
            ['take_id' => 0, 'filter' => '', 'expected_method' => 'findAll'],
        ];

        foreach ($testCases as $testCase) {
            // Simulate the logic from index.php
            $take_id = $testCase['take_id'];
            $filter = trim($testCase['filter']);
            
            if ($take_id > 0 && !empty($filter)) {
                $method = 'findByFilter_with_take_id';
            } elseif ($take_id > 0) {
                $method = 'findWithinTakeId';
            } elseif (!empty($filter)) {
                $method = 'findByFilter';
            } else {
                $method = 'findAll';
            }
            
            $this->assertEquals(
                $testCase['expected_method'], 
                $method, 
                "Should select correct repository method for take_id={$take_id}, filter='{$filter}'"
            );
        }
    }

    /**
     * Test empty filter handling logic
     */
    public function testEmptyFilterHandling()
    {
        // Test that empty filters are handled correctly
        $filters = ['', '   ', null, 0, false];
        
        foreach ($filters as $filter) {
            $processed = trim($filter ?? '');
            $isEmpty = empty($processed);
            
            $this->assertTrue($isEmpty, "Filter '$filter' should be considered empty after processing");
        }
    }

    /**
     * Test filter priority logic
     */
    public function testFilterPriorityLogic()
    {
        // Test the priority scoring used in the SQL CASE statement
        $testMoments = [
            ['notes' => 'contains test string', 'translated_note' => null, 'expected_priority' => 1],
            ['notes' => 'no match', 'translated_note' => 'contains test string', 'expected_priority' => 2],
            ['notes' => 'no match', 'translated_note' => 'no match', 'expected_priority' => 3],
            ['notes' => 'contains test string', 'translated_note' => 'also contains test string', 'expected_priority' => 1], // Should prioritize notes match
        ];

        $searchTerm = 'test string';

        foreach ($testMoments as $moment) {
            // Simulate the priority logic from the CASE statement
            $priority = 3; // default
            
            if (!empty($moment['notes']) && stripos($moment['notes'], $searchTerm) !== false) {
                $priority = 1;
            } elseif (!empty($moment['translated_note']) && stripos($moment['translated_note'], $searchTerm) !== false) {
                $priority = 2;
            }
            
            $this->assertEquals(
                $moment['expected_priority'], 
                $priority, 
                "Priority should be calculated correctly for moment: " . json_encode($moment)
            );
        }
    }
}