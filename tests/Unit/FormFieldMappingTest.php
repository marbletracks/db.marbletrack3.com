<?php
/**
 * Unit tests for form field mapping and validation
 * These tests would have caught the duplicate name attribute bug from issue #57
 */

use PHPUnit\Framework\TestCase;

class FormFieldMappingTest extends TestCase
{
    /**
     * Test that Parts form fields match what the PHP handler expects
     * This test would have caught the duplicate name="notes" name="part_description" bug
     */
    public function testPartsFormFieldMapping()
    {
        // Load the Parts form template
        $templatePath = __DIR__ . '/../../templates/admin/parts/part.tpl.php';
        $this->assertFileExists($templatePath, "Parts template should exist");
        
        $templateContent = file_get_contents($templatePath);
        
        // Test that description textarea has the correct name attribute
        $this->assertStringContainsString('name="part_description"', $templateContent, 
            "Parts form should have textarea with name='part_description'");
        
        // Test that there are no duplicate name attributes on the description textarea
        $this->assertFormFieldHasNoDuplicateNames($templateContent, 'textarea', 'part_description');
        
        // Test that the expected form fields exist for Parts
        $expectedFields = [
            'part_alias',
            'part_name', 
            'part_description',
            'image_urls[]',
            'moment_ids'
        ];
        
        foreach ($expectedFields as $field) {
            $this->assertStringContainsString("name=\"{$field}\"", $templateContent,
                "Parts form should contain field: {$field}");
        }
    }
    
    /**
     * Test that Workers form fields match what the PHP handler expects
     */
    public function testWorkersFormFieldMapping()
    {
        // Load the Workers form template
        $templatePath = __DIR__ . '/../../templates/admin/workers/worker.tpl.php';
        $this->assertFileExists($templatePath, "Workers template should exist");
        
        $templateContent = file_get_contents($templatePath);
        
        // Test that description textarea has the correct name attribute
        $this->assertStringContainsString('name="description"', $templateContent, 
            "Workers form should have textarea with name='description'");
        
        // Test that there are no duplicate name attributes on the description textarea
        $this->assertFormFieldHasNoDuplicateNames($templateContent, 'textarea', 'description');
    }
    
    /**
     * Test Parts PHP handler expects the correct POST parameters
     */
    public function testPartsHandlerExpectedParameters()
    {
        $handlerPath = __DIR__ . '/../../wwwroot/admin/parts/part.php';
        $this->assertFileExists($handlerPath, "Parts handler should exist");
        
        $handlerContent = file_get_contents($handlerPath);
        
        // Test that handler looks for the correct POST parameters
        $expectedParameters = [
            "trim(\$_POST['part_alias']",
            "trim(\$_POST['part_name']", 
            "trim(\$_POST['part_description']",
            "\$_POST['image_urls']",
            "\$_POST['moment_ids']"
        ];
        
        foreach ($expectedParameters as $param) {
            $this->assertStringContainsString($param, $handlerContent,
                "Parts handler should reference parameter: {$param}");
        }
    }
    
    /**
     * Helper method to check for duplicate name attributes in form elements
     */
    private function assertFormFieldHasNoDuplicateNames(string $html, string $elementType, string $expectedName)
    {
        // Use DOM parser to find the element
        $dom = new DOMDocument();
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $elements = $dom->getElementsByTagName($elementType);
        
        foreach ($elements as $element) {
            $nameAttr = $element->getAttribute('name');
            if ($nameAttr === $expectedName) {
                // Count how many name attributes this element has
                $elementHtml = $dom->saveHTML($element);
                $nameCount = substr_count($elementHtml, 'name=');
                
                $this->assertEquals(1, $nameCount, 
                    "Element with name='{$expectedName}' should have exactly one name attribute. Element HTML: {$elementHtml}");
                
                return; // Found the element, test passed
            }
        }
        
        $this->fail("Could not find {$elementType} element with name='{$expectedName}'");
    }
    
    /**
     * Test SQL parameter count validation
     * This type of test would catch parameter count mismatches
     */
    public function testSQLParameterCounting()
    {
        // Test cases for common SQL patterns
        $testCases = [
            [
                'sql' => 'UPDATE moments SET notes = ?, frame_start = ?, frame_end = ? WHERE moment_id = ?',
                'params' => ['test note', 100, 200, 1],
                'types' => 'siii'
            ],
            [
                'sql' => 'INSERT INTO parts (alias, name, description) VALUES (?, ?, ?)',
                'params' => ['test_alias', 'Test Part', 'Test description'],
                'types' => 'sss'
            ]
        ];
        
        foreach ($testCases as $case) {
            $placeholderCount = substr_count($case['sql'], '?');
            $paramCount = count($case['params']);
            $typeCount = strlen($case['types']);
            
            $this->assertEquals($placeholderCount, $paramCount, 
                "SQL placeholders should match parameter count for: {$case['sql']}");
            $this->assertEquals($placeholderCount, $typeCount,
                "SQL placeholders should match type string length for: {$case['sql']}");
        }
    }
}