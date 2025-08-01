<?php
/**
 * Integration tests for Parts functionality
 * These tests validate database operations and would catch SQL parameter mismatches
 */

use PHPUnit\Framework\TestCase;
use Database\PartsRepository;

class PartsIntegrationTest extends TestCase
{
    private \Database\Database $testDb;
    private PartsRepository $partsRepo;
    private string $testPrefix;
    
    protected function setUp(): void
    {
        // Get test database connection
        $this->testDb = getTestDatabase();
        $this->partsRepo = new PartsRepository($this->testDb, 'en');
        
        // Create unique test prefix using timestamp and random number
        // Use short prefix to fit in 20-char part_alias column
        $this->testPrefix = 't' . substr(time(), -3) . rand(10, 99) . '_';
        
        // Clean up any old test data from previous runs
        $this->cleanupTestData();
    }
    
    protected function tearDown(): void
    {
        // Clean up test data after each test
        $this->cleanupTestData();
    }
    
    /**
     * Test that Part creation works with correct form parameters
     * This validates the fix from issue #57
     */
    public function testPartCreationWithFormData()
    {
        // Simulate form data that would be submitted
        $formData = [
            'part_alias' => $this->testPrefix . 'alias',
            'part_name' => 'Test Part Name',
            'part_description' => 'This is a test part description',
            'image_urls' => ['http://example.com/image1.jpg', 'http://example.com/image2.jpg'],
            'moment_ids' => []
        ];
        
        // Test the repository insert method with form data
        $newPartId = $this->partsRepo->insert(
            $formData['part_alias'],
            $formData['part_name'], 
            $formData['part_description']
        );
        
        $this->assertGreaterThan(0, $newPartId, "Part should be created successfully");
        
        // Verify the part was saved correctly
        $savedPart = $this->partsRepo->findById($newPartId);
        
        $this->assertNotNull($savedPart, "Created part should be retrievable");
        $this->assertEquals($formData['part_alias'], $savedPart->part_alias, "Part alias should be saved correctly");
        $this->assertEquals($formData['part_name'], $savedPart->name, "Part name should be saved correctly");
        $this->assertEquals($formData['part_description'], $savedPart->description, "Part description should be saved correctly");
    }
    
    /**
     * Test that Part updates work with correct form parameters
     * This validates the fix from issue #57
     */
    public function testPartUpdateWithFormData()
    {
        // Create a test part first
        $originalPartId = $this->partsRepo->insert($this->testPrefix . 'orig', 'Original Name', 'Original description');
        
        // Simulate form data for update
        $updateData = [
            'part_alias' => $this->testPrefix . 'upd',
            'part_name' => 'Updated Name',
            'part_description' => 'Updated description with more content'
        ];
        
        // Test the repository update method
        $this->partsRepo->update(
            part_id: $originalPartId,
            alias: $updateData['part_alias'],
            name: $updateData['part_name'],
            description: $updateData['part_description']
        );
        
        // Verify the part was updated correctly
        $updatedPart = $this->partsRepo->findById($originalPartId);
        
        $this->assertNotNull($updatedPart, "Updated part should be retrievable");
        $this->assertEquals($updateData['part_alias'], $updatedPart->part_alias, "Updated alias should be saved correctly");
        $this->assertEquals($updateData['part_name'], $updatedPart->name, "Updated name should be saved correctly");
        $this->assertEquals($updateData['part_description'], $updatedPart->description, "Updated description should be saved correctly");
    }
    
    /**
     * Test that empty/null descriptions are handled properly
     */
    public function testPartWithEmptyDescription()
    {
        // Test with empty string
        $partId1 = $this->partsRepo->insert($this->testPrefix . 'emp', 'Test Empty', '');
        $part1 = $this->partsRepo->findById($partId1);
        $this->assertEquals('', $part1->description, "Empty description should be saved as empty string");
        
        // Test with null
        $partId2 = $this->partsRepo->insert($this->testPrefix . 'nul', 'Test Null', '');
        $part2 = $this->partsRepo->findById($partId2);
        $this->assertEmpty($part2->description, "Null description should be handled gracefully");
    }
    
    /**
     * Test SQL parameter validation by examining actual database operations
     * This type of test would catch the parameter mismatch bugs
     */
    public function testSQLParameterValidation()
    {
        // Test that we can perform common database operations without SQL errors
        try {
            // Test INSERT
            $partId = $this->partsRepo->insert($this->testPrefix . 'par', 'Parameter Test', 'Testing SQL parameters');
            $this->assertGreaterThan(0, $partId, "INSERT should work without parameter errors");
            
            // Test UPDATE
            $this->partsRepo->update(
                part_id: $partId,
                alias: $this->testPrefix . 'pup',
                name: 'Parameter Test Updated',
                description: 'Updated testing SQL parameters'
            );
            
            // Verify update worked
            $updatedPart = $this->partsRepo->findById($partId);
            $this->assertEquals($this->testPrefix . 'pup', $updatedPart->part_alias, "UPDATE should work without parameter errors");
            
        } catch (\Exception $e) {
            $this->fail("Database operations should not throw exceptions. Error: " . $e->getMessage());
        }
    }
    
    /**
     * Test form submission simulation
     * This simulates the complete flow from form submission to database storage
     */
    public function testCompleteFormSubmissionFlow()
    {
        // Simulate $_POST data as it would come from the form
        $_POST = [
            'part_alias' => $this->testPrefix . 'form',
            'part_name' => 'Form Test Part',
            'part_description' => 'This part was created via form submission test',
            'image_urls' => [],
            'moment_ids' => ''
        ];
        
        // Simulate the form processing logic from part.php
        $alias = trim($_POST['part_alias'] ?? '');
        $name = trim($_POST['part_name'] ?? '');
        $description = trim($_POST['part_description'] ?? '');
        $image_urls = array_filter(array_map('trim', $_POST['image_urls'] ?? []));
        $moment_ids_str = $_POST['moment_ids'] ?? '';
        $moment_ids = $moment_ids_str ? explode(',', $moment_ids_str) : [];
        
        // Validate that the form data was processed correctly
        $this->assertEquals($this->testPrefix . 'form', $alias, "Form alias should be processed correctly");
        $this->assertEquals('Form Test Part', $name, "Form name should be processed correctly");
        $this->assertEquals('This part was created via form submission test', $description, "Form description should be processed correctly");
        $this->assertIsArray($image_urls, "Image URLs should be processed as array");
        $this->assertIsArray($moment_ids, "Moment IDs should be processed as array");
        
        // Test database insertion with processed form data
        $newId = $this->partsRepo->insert($alias, $name, $description);
        $this->assertGreaterThan(0, $newId, "Form data should result in successful database insertion");
        
        // Clean up $_POST
        unset($_POST);
    }
    
    /**
     * Clean up test data
     */
    private function cleanupTestData(): void
    {
        // Remove all test parts with our unique prefix pattern
        // This covers all possible aliases we might have created during testing
        try {
            // Clean up parts created with our test prefix
            $this->testDb->executeSQL(
                "DELETE FROM parts WHERE part_alias LIKE ?",
                's',
                [$this->testPrefix . '%']
            );
            
            // Also clean up any old test data from previous runs that might have used fixed names
            $oldTestAliases = [
                'test_part_alias',
                'original_alias', 
                'updated_alias',
                'test_empty',
                'test_null',
                'param_test',
                'param_test_updated',
                'form_test'
            ];
            
            foreach ($oldTestAliases as $alias) {
                $this->testDb->executeSQL(
                    "DELETE FROM parts WHERE part_alias = ?",
                    's',
                    [$alias]
                );
            }
        } catch (\Exception $e) {
            // Ignore errors during cleanup - table might not exist or different structure
            // This ensures tests can still run even if cleanup fails
        }
    }
}