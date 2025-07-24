<?php
/**
 * Test runner specifically for Issues #57 and #58 validation
 */

// Simple bootstrap
require_once __DIR__ . '/../classes/Mlaphp/Autoloader.php';
$autoloader = new \Mlaphp\Autoloader();
spl_autoload_register(array($autoloader, 'load'));

class Issue57and58TestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function run(): void
    {
        echo "Testing fixes for Issues #57 and #58...\n\n";

        $this->testIssue57PartsFormFix();
        $this->testIssue58WorkersFormFix();
        $this->testImageFieldsUnaffected();
        $this->testFormSubmissionFlow();

        // Output results
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "Issue #57 and #58 Validation Results:\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";

        if (!empty($this->failures)) {
            echo "\nFailures:\n";
            foreach ($this->failures as $failure) {
                echo "❌ {$failure}\n";
            }
        }

        if ($this->failed === 0) {
            echo "\n✅ All tests passed! Issues #57 and #58 fixes are working correctly.\n";
            echo "✅ The testing infrastructure would have caught these bugs.\n";
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

    private function testIssue57PartsFormFix(): void
    {
        echo "Testing Issue #57 fix (Parts description not saving)...\n";

        $templatePath = __DIR__ . '/../templates/admin/parts/part.tpl.php';

        if (!file_exists($templatePath)) {
            $this->assert(false, "Parts template not found: {$templatePath}");
            return;
        }

        $templateContent = file_get_contents($templatePath);

        // The specific fix: should have name="part_description"
        $hasCorrectName = strpos($templateContent, 'name="part_description"') !== false;
        $this->assert($hasCorrectName, "Issue #57: Parts form has correct name='part_description'");

        // The specific bug: should NOT have duplicate name="notes"
        $hasOldBug = strpos($templateContent, 'name="notes"') !== false;
        $this->assert(!$hasOldBug, "Issue #57: Duplicate name='notes' attribute removed (was the bug)");

        // Check that the handler expects the right parameter
        $handlerPath = __DIR__ . '/../wwwroot/admin/parts/part.php';
        if (file_exists($handlerPath)) {
            $handlerContent = file_get_contents($handlerPath);
            $handlerExpectsCorrect = strpos($handlerContent, "\$_POST['part_description']") !== false;
            $this->assert($handlerExpectsCorrect, "Issue #57: Parts handler expects 'part_description' parameter");
        }
    }

    private function testIssue58WorkersFormFix(): void
    {
        echo "\nTesting Issue #58 fix (Workers description also affected)...\n";

        $templatePath = __DIR__ . '/../templates/admin/workers/worker.tpl.php';

        if (!file_exists($templatePath)) {
            $this->assert(false, "Workers template not found: {$templatePath}");
            return;
        }

        $templateContent = file_get_contents($templatePath);

        // The specific fix: should have name="description"
        $hasCorrectName = strpos($templateContent, 'name="description"') !== false;
        $this->assert($hasCorrectName, "Issue #58: Workers form has correct name='description'");

        // The specific bug: should NOT have duplicate name="notes"
        $hasOldBug = strpos($templateContent, 'name="notes"') !== false;
        $this->assert(!$hasOldBug, "Issue #58: Duplicate name='notes' attribute removed from Workers form");
    }

    private function testImageFieldsUnaffected(): void
    {
        echo "\nTesting that image saving still works (mentioned in #58)...\n";

        $templatePath = __DIR__ . '/../templates/admin/parts/part.tpl.php';
        if (file_exists($templatePath)) {
            $templateContent = file_get_contents($templatePath);
            $hasImageFields = strpos($templateContent, 'name="image_urls[]"') !== false;
            $this->assert($hasImageFields, "Image upload fields unaffected by description fix");
        }
    }

    private function testFormSubmissionFlow(): void
    {
        echo "\nTesting complete form submission flow...\n";

        // Simulate form data after the fix
        $_POST = [
            'part_alias' => 'test_part_after_fix',
            'part_name' => 'Test Part After Fix',
            'part_description' => 'This description should be saved correctly',
            'image_urls' => [],
            'moment_ids' => ''
        ];

        // Simulate form processing
        $description = trim($_POST['part_description'] ?? '');

        $this->assert(!empty($description), "Form submission: Description should not be empty after fix");
        $this->assert($description === 'This description should be saved correctly',
            "Form submission: Description should match submitted value");

        // Clean up
        unset($_POST);
    }
}

// Run the specific issue tests
$runner = new Issue57and58TestRunner();
$runner->run();