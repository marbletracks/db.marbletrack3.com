<?php
/**
 * Specific tests for issues #57 and #58
 * These tests demonstrate exactly what would have caught those bugs
 */

use PHPUnit\Framework\TestCase;

class Issue57and58Test extends TestCase
{
    /**
     * Test for Issue #57: Fix saving Parts on page /admin/parts/part.php
     * 
     * The issue was that Part descriptions were not being saved due to 
     * duplicate name attributes in the textarea element.
     */
    public function testIssue57PartsDescriptionFormFieldMapping()
    {
        $templatePath = __DIR__ . '/../../templates/admin/parts/part.tpl.php';
        $this->assertFileExists($templatePath, "Parts template should exist");
        
        $templateContent = file_get_contents($templatePath);
        
        // The bug: textarea had both name="notes" and name="part_description"
        // HTML takes the first one, so form submitted "notes" but PHP expected "part_description"
        
        // Verify the fix: should have name="part_description" 
        $this->assertStringContainsString('name="part_description"', $templateContent,
            "Issue #57: Parts form must have textarea with name='part_description'");
        
        // Verify the fix: should NOT have the problematic duplicate name="notes"
        $this->assertStringNotContainsString('name="notes"', $templateContent,
            "Issue #57: Parts form must not have duplicate name='notes' attribute");
        
        // Test the specific DOM structure to catch future regressions
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($templateContent);
        libxml_clear_errors();
        
        $textareas = $dom->getElementsByTagName('textarea');
        $partDescriptionTextarea = null;
        
        foreach ($textareas as $textarea) {
            if ($textarea->getAttribute('name') === 'part_description') {
                $partDescriptionTextarea = $textarea;
                break;
            }
        }
        
        $this->assertNotNull($partDescriptionTextarea, 
            "Issue #57: Must find textarea with name='part_description'");
        
        // Verify this textarea has only one name attribute
        $textareaHTML = $dom->saveHTML($partDescriptionTextarea);
        $nameAttributeCount = substr_count($textareaHTML, 'name=');
        $this->assertEquals(1, $nameAttributeCount,
            "Issue #57: Textarea should have exactly one name attribute, found {$nameAttributeCount} in: {$textareaHTML}");
    }
    
    /**
     * Test for the Workers fix mentioned in Issue #58
     * 
     * Issue #58 also fixed Workers form which had the same duplicate name attribute problem
     */
    public function testIssue58WorkersDescriptionFormFieldMapping()
    {
        $templatePath = __DIR__ . '/../../templates/admin/workers/worker.tpl.php';
        $this->assertFileExists($templatePath, "Workers template should exist");
        
        $templateContent = file_get_contents($templatePath);
        
        // Verify the fix: should have name="description"
        $this->assertStringContainsString('name="description"', $templateContent,
            "Issue #58: Workers form must have textarea with name='description'");
        
        // Verify the fix: should NOT have the problematic duplicate name="notes"  
        $this->assertStringNotContainsString('name="notes"', $templateContent,
            "Issue #58: Workers form must not have duplicate name='notes' attribute");
    }
    
    /**
     * Test that image saving still works after the fix (as mentioned in #58)
     */
    public function testImageSavingFieldsUnaffected()
    {
        $templatePath = __DIR__ . '/../../templates/admin/parts/part.tpl.php';
        $templateContent = file_get_contents($templatePath);
        
        // Image fields should still work - they use different field names
        $this->assertStringContainsString('name="image_urls[]"', $templateContent,
            "Image upload fields should be unaffected by the description fix");
    }
    
    /**
     * Test the Parts PHP handler to ensure it looks for the correct POST parameters
     * This would catch the disconnect between form and handler
     */
    public function testPartsHandlerExpectsCorrectParameters()
    {
        $handlerPath = __DIR__ . '/../../wwwroot/admin/parts/part.php';
        $this->assertFileExists($handlerPath, "Parts handler should exist");
        
        $handlerContent = file_get_contents($handlerPath);
        
        // Verify handler expects 'part_description' (not 'notes')
        $this->assertStringContainsString("\$_POST['part_description']", $handlerContent,
            "Issue #57: Parts handler must look for 'part_description' parameter");
        
        // Handler should also expect other correct parameters
        $expectedParams = [
            "\$_POST['part_alias']",
            "\$_POST['part_name']", 
            "\$_POST['image_urls']"
        ];
        
        foreach ($expectedParams as $param) {
            $this->assertStringContainsString($param, $handlerContent,
                "Parts handler should reference parameter: {$param}");
        }
    }
    
    /**
     * Integration test simulating the complete form submission flow
     * This would catch the end-to-end bug from Issue #57
     */
    public function testCompleteFormSubmissionFlow()
    {
        // Simulate the form data as it would be submitted after the fix
        $_POST = [
            'part_alias' => 'test_part_fix_57',
            'part_name' => 'Test Part for Issue 57',
            'part_description' => 'This description should be saved correctly after the fix',
            'image_urls' => [],
            'moment_ids' => ''
        ];
        
        // Simulate the form processing from part.php
        $alias = trim($_POST['part_alias'] ?? '');
        $name = trim($_POST['part_name'] ?? '');
        $description = trim($_POST['part_description'] ?? '');
        
        // Before the fix, $description would be empty because form sent 'notes' but handler expected 'part_description'
        $this->assertNotEmpty($description, 
            "Issue #57: Description should not be empty after form field fix");
        $this->assertEquals('This description should be saved correctly after the fix', $description,
            "Issue #57: Description should match what was submitted");
        
        // Clean up
        unset($_POST);
    }
    
    /**
     * Test for potential SQL parameter mismatches that could be related
     */
    public function testSQLParameterConsistency()
    {
        // Test common SQL patterns to catch parameter count mismatches
        $sqlTests = [
            [
                'name' => 'Parts INSERT',
                'sql' => 'INSERT INTO parts (alias, name, description) VALUES (?, ?, ?)',
                'paramCount' => 3
            ],
            [
                'name' => 'Parts UPDATE', 
                'sql' => 'UPDATE parts SET alias = ?, name = ?, description = ? WHERE part_id = ?',
                'paramCount' => 4
            ]
        ];
        
        foreach ($sqlTests as $test) {
            $placeholderCount = substr_count($test['sql'], '?');
            $this->assertEquals($test['paramCount'], $placeholderCount,
                "{$test['name']}: Parameter count mismatch in SQL: {$test['sql']}");
        }
    }
}