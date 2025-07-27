<?php
/**
 * Master Test Runner for Marble Track 3
 * 
 * Runs all available tests in quiet mode by default.
 * Use --verbose flag to see detailed output from all tests.
 * 
 * Usage:
 *   php run_all_tests.php           # Quiet mode (only failures/warnings)
 *   php run_all_tests.php --verbose # Verbose mode (all output)
 *   php run_all_tests.php --fast    # Skip database-dependent tests
 */

// Check for flags
$verbose = in_array('--verbose', $argv) || in_array('-v', $argv);
$fast = in_array('--fast', $argv) || in_array('-f', $argv);

echo "🧪 Marble Track 3 Test Suite\n";
echo str_repeat("=", 50) . "\n";

if ($verbose) {
    echo "Running in VERBOSE mode - showing all test output\n";
} else {
    echo "Running in QUIET mode - showing only failures and warnings\n";
    echo "Use --verbose flag for detailed output\n";
}

if ($fast) {
    echo "Running in FAST mode - skipping database tests\n";
}

echo "\n";

$testsRun = 0;
$testsPassed = 0;
$testsFailed = 0;
$totalStartTime = microtime(true);

function runTest(string $testName, string $command, bool $verbose = false): array
{
    echo "🔄 Running {$testName}...\n";
    $startTime = microtime(true);
    
    if ($verbose) {
        echo "\n";
        system($command, $returnCode);
        echo "\n";
    } else {
        $output = [];
        exec($command, $output, $returnCode);
        
        // In quiet mode, only show output if there are issues
        $hasIssues = $returnCode !== 0;
        foreach ($output as $line) {
            if (strpos($line, '❌') !== false || strpos($line, '⚠️') !== false || strpos($line, 'Failed:') !== false) {
                $hasIssues = true;
                break;
            }
        }
        
        if ($hasIssues) {
            echo "  Issues found in {$testName}:\n";
            foreach ($output as $line) {
                if (strpos($line, '❌') !== false || strpos($line, '⚠️') !== false || 
                    strpos($line, 'Failed:') !== false || strpos($line, 'Passed:') !== false ||
                    strpos($line, 'Results') !== false) {
                    echo "  {$line}\n";
                }
            }
        } else {
            echo "  ✅ {$testName} passed\n";
        }
    }
    
    $duration = round((microtime(true) - $startTime) * 1000);
    
    return [
        'passed' => $returnCode === 0,
        'duration' => $duration
    ];
}

// Test 1: Simple form validation tests
$result = runTest(
    'Form Field Validation',
    'php ' . __DIR__ . '/run_simple_tests.php',
    $verbose
);
$testsRun++;
if ($result['passed']) $testsPassed++; else $testsFailed++;

// Test 2: Issues #57/#58 regression tests
$result = runTest(
    'Issues #57/#58 Regression',
    'php ' . __DIR__ . '/test_issues_57_58.php',
    $verbose
);
$testsRun++;
if ($result['passed']) $testsPassed++; else $testsFailed++;

// Test 3: Repository SQL parameter validation
$verboseFlag = $verbose ? ' --verbose' : '';
$result = runTest(
    'Repository SQL Parameters',
    'php ' . __DIR__ . '/test_repository_sql_parameters.php' . $verboseFlag,
    false // Always use our custom quiet mode
);
$testsRun++;
if ($result['passed']) $testsPassed++; else $testsFailed++;

// Test 4: AJAX endpoint security
$result = runTest(
    'AJAX Endpoint Security',
    'php ' . __DIR__ . '/test_ajax_endpoints.php' . $verboseFlag,
    false // Always use our custom quiet mode
);
$testsRun++;
if ($result['passed']) $testsPassed++; else $testsFailed++;

// Test 5: Database connectivity (unless --fast)
if (!$fast) {
    $result = runTest(
        'Test Database Connectivity',
        'php ' . __DIR__ . '/setup_test_database.php check',
        $verbose
    );
    $testsRun++;
    if ($result['passed']) $testsPassed++; else $testsFailed++;
} else {
    echo "⏩ Skipping database tests (--fast mode)\n";
}

// Final summary
$totalDuration = round((microtime(true) - $totalStartTime) * 1000);

echo "\n" . str_repeat("=", 50) . "\n";
echo "🏁 Test Suite Complete\n";
echo "Tests run: {$testsRun}\n";
echo "Passed: {$testsPassed}\n";
echo "Failed: {$testsFailed}\n";
echo "Duration: {$totalDuration}ms\n";

if ($testsFailed === 0) {
    echo "\n🎉 All tests passed! Your codebase is looking good.\n";
    exit(0);
} else {
    echo "\n❌ {$testsFailed} test(s) failed. Please review the issues above.\n";
    
    if (!$verbose) {
        echo "💡 Run with --verbose flag for detailed output.\n";
    }
    
    exit(1);
}