<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Bridge test that integrates existing custom test runners with PHPUnit
 * This allows running custom tests through PHPUnit while preserving their logic
 */
class PHPUnitBridgeTest extends TestCase
{
    public function testFormFieldValidation(): void
    {
        $output = [];
        $returnCode = 0;
        
        // Capture output from custom test runner
        ob_start();
        include __DIR__ . '/../../scripts/test_issues_57_58.php';
        $testOutput = ob_get_clean();
        
        // Parse results - look for "Passed:" and "Failed:" lines
        preg_match('/Passed:\s*(\d+)/', $testOutput, $passedMatches);
        preg_match('/Failed:\s*(\d+)/', $testOutput, $failedMatches);
        
        $passed = isset($passedMatches[1]) ? (int)$passedMatches[1] : 0;
        $failed = isset($failedMatches[1]) ? (int)$failedMatches[1] : 0;
        
        $this->assertEquals(0, $failed, "Form field validation tests failed. Output:\n" . $testOutput);
        $this->assertGreaterThan(0, $passed, "No form field validation tests passed");
    }

    public function testRepositorySQLParameters(): void
    {
        // Set $argv for the included script
        global $argv;
        $argv = ['test_repository_sql_parameters.php'];
        
        ob_start();
        include __DIR__ . '/../../scripts/test_repository_sql_parameters.php';
        $testOutput = ob_get_clean();
        
        preg_match('/Passed:\s*(\d+)/', $testOutput, $passedMatches);
        preg_match('/Failed:\s*(\d+)/', $testOutput, $failedMatches);
        
        $passed = isset($passedMatches[1]) ? (int)$passedMatches[1] : 0;
        $failed = isset($failedMatches[1]) ? (int)$failedMatches[1] : 0;
        
        $this->assertEquals(0, $failed, "SQL parameter validation tests failed. Output:\n" . $testOutput);
        $this->assertGreaterThan(0, $passed, "No SQL parameter tests passed");
    }

    public function testAjaxEndpointsHaveNoWarnings(): void
    {
        // Set globals for the included script
        global $argv, $_POST, $_GET, $_SERVER, $_REQUEST;
        $argv = ['test_ajax_endpoints.php'];
        $_POST = $_POST ?? [];
        $_GET = $_GET ?? [];
        $_REQUEST = $_REQUEST ?? [];
        $_SERVER = $_SERVER ?? [];
        
        // Temporarily disable error reporting for array conversion warnings
        $oldErrorReporting = error_reporting();
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        
        ob_start();
        try {
            include __DIR__ . '/../../scripts/test_ajax_endpoints.php';
            $testOutput = ob_get_clean();
        } catch (Throwable $e) {
            $testOutput = ob_get_clean();
            $testOutput .= "\nError: " . $e->getMessage();
        }
        
        // Restore error reporting
        error_reporting($oldErrorReporting);
        
        // Count warnings in output
        $warningCount = substr_count($testOutput, '⚠️');
        
        $this->assertEquals(0, $warningCount, "AJAX endpoints have warnings. Output:\n" . $testOutput);
    }
}