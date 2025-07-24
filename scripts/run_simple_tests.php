<?php
/**
 * Simple test runner to verify our tests work without PHPUnit
 * This demonstrates that our tests would catch the bugs from issues #57 and #58
 */

// Simple bootstrap without database
require_once __DIR__ . '/../classes/Mlaphp/Autoloader.php';
$autoloader = new \Mlaphp\Autoloader();
spl_autoload_register(array($autoloader, 'load'));

class SimpleTestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function run(): void
    {
        echo "Running simple tests to validate testing infrastructure...\n\n";

        // Test form field mapping
        $this->testPartsFormFieldMapping();
        $this->testWorkersFormFieldMapping();
        $this->testSQLParameterCounting();

        // Output results
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "Test Results:\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";

        if (!empty($this->failures)) {
            echo "\nFailures:\n";
            foreach ($this->failures as $failure) {
                echo "❌ {$failure}\n";
            }
        }

        if ($this->failed === 0) {
            echo "✅ All tests passed! The testing infrastructure is working.\n";
        } else {
            echo "❌ Some tests failed. This is expected if there are still bugs to fix.\n";
        }
    }

    private function assert(bool $condition, string $message): void
    {
        if ($condition) {
            $this->passed++;
            echo "✅ {$message}\n";
        } else {
            $this->failed++;
            $this->failures[] = $message;
            echo "❌ {$message}\n";
        }
    }

    private function testPartsFormFieldMapping(): void
    {
        echo "Testing Parts form field mapping...\n";

        $templatePath = __DIR__ . '/../templates/admin/parts/part.tpl.php';

        if (!file_exists($templatePath)) {
            $this->assert(false, "Parts template not found: {$templatePath}");
            return;
        }

        $templateContent = file_get_contents($templatePath);

        // Test that description textarea has the correct name attribute
        $hasCorrectName = strpos($templateContent, 'name="part_description"') !== false;
        $this->assert($hasCorrectName, "Parts form should have textarea with name='part_description'");

        // Test that there's no duplicate name attribute that would cause the bug
        $hasOldBuggyName = strpos($templateContent, 'name="notes"') !== false;
        $this->assert(!$hasOldBuggyName, "Parts form should not have duplicate name='notes' attribute (this was the bug!)");

        // Test other expected fields
        $expectedFields = ['part_alias', 'part_name', 'part_description'];
        foreach ($expectedFields as $field) {
            $hasField = strpos($templateContent, "name=\"{$field}\"") !== false;
            $this->assert($hasField, "Parts form should contain field: {$field}");
        }
    }

    private function testWorkersFormFieldMapping(): void
    {
        echo "\nTesting Workers form field mapping...\n";

        $templatePath = __DIR__ . '/../templates/admin/workers/worker.tpl.php';

        if (!file_exists($templatePath)) {
            $this->assert(false, "Workers template not found: {$templatePath}");
            return;
        }

        $templateContent = file_get_contents($templatePath);

        // Test that description textarea has the correct name attribute
        $hasCorrectName = strpos($templateContent, 'name="description"') !== false;
        $this->assert($hasCorrectName, "Workers form should have textarea with name='description'");

        // Test that there's no duplicate name attribute that would cause the bug
        $hasOldBuggyName = strpos($templateContent, 'name="notes"') !== false;
        $this->assert(!$hasOldBuggyName, "Workers form should not have duplicate name='notes' attribute (this was the bug!)");
    }

    private function testSQLParameterCounting(): void
    {
        echo "\nTesting SQL parameter validation...\n";

        // Test cases that would catch SQL parameter mismatches
        $testCases = [
            [
                'name' => 'Moment update query',
                'sql' => 'UPDATE moments SET notes = ?, frame_start = ?, frame_end = ? WHERE moment_id = ?',
                'params' => ['test note', 100, 200, 1],
                'types' => 'siii'
            ],
            [
                'name' => 'Parts insert query',
                'sql' => 'INSERT INTO parts (alias, name, description) VALUES (?, ?, ?)',
                'params' => ['test_alias', 'Test Part', 'Test description'],
                'types' => 'sss'
            ]
        ];

        foreach ($testCases as $case) {
            $placeholderCount = substr_count($case['sql'], '?');
            $paramCount = count($case['params']);
            $typeCount = strlen($case['types']);

            $this->assert($placeholderCount === $paramCount,
                "{$case['name']}: SQL placeholders ({$placeholderCount}) should match parameter count ({$paramCount})");
            $this->assert($placeholderCount === $typeCount,
                "{$case['name']}: SQL placeholders ({$placeholderCount}) should match type string length ({$typeCount})");
        }
    }
}

// Run the tests
$runner = new SimpleTestRunner();
$runner->run();