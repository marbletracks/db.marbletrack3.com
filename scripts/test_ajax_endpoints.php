<?php
/**
 * AJAX Endpoint Validation Tests
 *
 * This test validates all AJAX endpoints for:
 * - Parameter validation and sanitization
 * - SQL injection protection
 * - Form field mapping correctness
 * - Error handling consistency
 * - Authentication requirements
 *
 * These are critical tests since AJAX endpoints handle user input directly
 * and could be vulnerable to the same issues found in #57/#58.
 */

// Bootstrap
require_once __DIR__ . '/../classes/Mlaphp/Autoloader.php';
$autoloader = new \Mlaphp\Autoloader();
spl_autoload_register(array($autoloader, 'load'));

class AjaxEndpointValidator
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];
    private array $warnings = [];
    private array $ajaxFiles = [];

    public function __construct()
    {
        $this->findAjaxFiles();
    }

    private function findAjaxFiles(): void
    {
        $ajaxDir = __DIR__ . '/../wwwroot/admin/ajax';
        $this->ajaxFiles = glob($ajaxDir . '/*.php');
    }

    public function run(): void
    {
        echo "=== AJAX Endpoint Security & Validation Test ===\n";
        echo "Testing " . count($this->ajaxFiles) . " AJAX endpoints for security and parameter validation...\n\n";

        foreach ($this->ajaxFiles as $file) {
            $this->validateAjaxEndpoint($file);
        }

        $this->validateSpecificEndpoints();
        $this->testParameterSanitization();
        $this->testSQLInjectionProtection();

        $this->outputResults();
    }

    private function validateAjaxEndpoint(string $filePath): void
    {
        $filename = basename($filePath);
        echo "🔍 Analyzing {$filename}...\n";

        $content = file_get_contents($filePath);

        // Test 1: Authentication Check
        $this->validateAuthentication($filename, $content);

        // Test 2: Content-Type Header
        $this->validateContentType($filename, $content);

        // Test 3: Input Validation
        $this->validateInputHandling($filename, $content);

        // Test 4: SQL Parameter Usage
        $this->validateSQLParameters($filename, $content);

        // Test 5: Error Handling
        $this->validateErrorHandling($filename, $content);

        echo "\n";
    }

    private function validateAuthentication(string $filename, string $content): void
    {
        $hasAuthCheck = strpos($content, '$is_logged_in->isLoggedIn()') !== false;
        $hasUnauthorizedResponse = strpos($content, '401') !== false || strpos($content, 'Unauthorized') !== false;

        if ($hasAuthCheck && $hasUnauthorizedResponse) {
            $this->passed++;
            echo "  ✅ Authentication: Proper login check and 401 response\n";
        } else if ($hasAuthCheck) {
            $this->passed++;
            $this->warnings[] = "{$filename}: Has auth check but unclear 401 response";
            echo "  ⚠️  Authentication: Has login check but unclear error response\n";
        } else {
            $this->failed++;
            $this->failures[] = "{$filename}: Missing authentication check";
            echo "  ❌ Authentication: No login check found\n";
        }
    }

    private function validateContentType(string $filename, string $content): void
    {
        $hasJsonHeader = strpos($content, "Content-Type: application/json") !== false;

        if ($hasJsonHeader) {
            $this->passed++;
            echo "  ✅ Headers: Proper JSON content-type set\n";
        } else {
            $this->failed++;
            $this->failures[] = "{$filename}: Missing JSON content-type header";
            echo "  ❌ Headers: Missing JSON content-type header\n";
        }
    }

    private function validateInputHandling(string $filename, string $content): void
    {
        // Check for proper input filtering
        $hasFilterInput = strpos($content, 'filter_input') !== false;
        $hasPostValidation = strpos($content, '$_POST') !== false;
        $hasValidationChecks = strpos($content, 'empty(') !== false || strpos($content, 'isset(') !== false;

        if ($hasFilterInput) {
            $this->passed++;
            echo "  ✅ Input: Uses filter_input for validation\n";
        } else if ($hasPostValidation && $hasValidationChecks) {
            $this->passed++;
            echo "  ✅ Input: Manual $_POST validation with checks\n";
        } else if ($hasPostValidation) {
            $this->warnings[] = "{$filename}: Uses $_POST but limited validation";
            echo "  ⚠️  Input: Uses $_POST with minimal validation\n";
        } else {
            echo "  ℹ️  Input: No obvious input handling found\n";
        }
    }

    private function validateSQLParameters(string $filename, string $content): void
    {
        // Find SQL queries with parameters
        $sqlPatterns = [
            '/executeSQL\s*\(\s*["\']([^"\']*)["\'],\s*["\']([^"\']*)["\']/',
            '/fetchResults\s*\(\s*["\']([^"\']*)["\'],\s*["\']([^"\']*)["\']/',
        ];

        $sqlFound = false;
        foreach ($sqlPatterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                $sqlFound = true;
                foreach ($matches as $match) {
                    $sql = $match[1];
                    $types = $match[2];

                    $placeholderCount = substr_count($sql, '?');
                    $typeCount = strlen($types);

                    if ($placeholderCount === $typeCount) {
                        $this->passed++;
                        echo "  ✅ SQL: Placeholders ({$placeholderCount}) match types ({$typeCount})\n";
                    } else {
                        $this->failed++;
                        $this->failures[] = "{$filename}: SQL placeholders ({$placeholderCount}) ≠ types ({$typeCount})";
                        echo "  ❌ SQL: Placeholders ({$placeholderCount}) ≠ types ({$typeCount})\n";
                    }
                }
            }
        }

        if (!$sqlFound) {
            echo "  ℹ️  SQL: No parameterized queries found\n";
        }
    }

    private function validateErrorHandling(string $filename, string $content): void
    {
        $hasErrorHandling = strpos($content, 'try') !== false && strpos($content, 'catch') !== false;
        $hasHttpErrors = strpos($content, 'http_response_code') !== false || strpos($content, 'header(') !== false;
        $hasJsonErrors = strpos($content, 'json_encode') !== false;

        if ($hasErrorHandling && $hasHttpErrors && $hasJsonErrors) {
            $this->passed++;
            echo "  ✅ Errors: Comprehensive error handling (try/catch + HTTP + JSON)\n";
        } else if ($hasJsonErrors && $hasHttpErrors) {
            $this->passed++;
            echo "  ✅ Errors: Good error handling (HTTP + JSON responses)\n";
        } else if ($hasJsonErrors) {
            echo "  ⚠️  Errors: Basic JSON error responses\n";
        } else {
            $this->warnings[] = "{$filename}: Limited error handling";
            echo "  ⚠️  Errors: Limited error handling found\n";
        }
    }

    private function validateSpecificEndpoints(): void
    {
        echo "=== Specific Endpoint Validation ===\n";

        $criticalEndpoints = [
            'save_moment_from_realtime.php' => [
                'description' => 'Saves moment data from real-time form',
                'expectedParams' => ['perspectives', 'frame_start', 'frame_end', 'notes'],
                'securityConcerns' => ['Mass assignment', 'SQL injection', 'XSS in notes']
            ],
            'update_moment_significance.php' => [
                'description' => 'Updates moment significance flags',
                'expectedParams' => ['moment_id', 'perspective_id', 'perspective_type', 'is_significant'],
                'securityConcerns' => ['Integer validation', 'Enum validation', 'Boolean conversion']
            ],
            'create_phrase_for_moment.php' => [
                'description' => 'Creates phrases from tokens',
                'expectedParams' => ['token_ids', 'phrase_string', 'moment_id'],
                'securityConcerns' => ['JSON injection', 'XSS in phrase text', 'Token ID validation']
            ],
            'create_moment_from_tokens.php' => [
                'description' => 'Creates moments from token selections',
                'expectedParams' => ['token_ids', 'action'],
                'securityConcerns' => ['Array injection', 'Bulk operations', 'Transaction safety']
            ]
        ];

        foreach ($criticalEndpoints as $filename => $info) {
            $filePath = __DIR__ . '/../wwwroot/admin/ajax/' . $filename;

            if (file_exists($filePath)) {
                echo "\n📋 {$filename} - {$info['description']}\n";
                $content = file_get_contents($filePath);

                // Check for expected parameters
                foreach ($info['expectedParams'] as $param) {
                    if (strpos($content, $param) !== false) {
                        echo "  ✅ Parameter '{$param}' found in code\n";
                        $this->passed++;
                    } else {
                        echo "  ⚠️  Parameter '{$param}' not found in code\n";
                        $this->warnings[] = "{$filename}: Expected parameter '{$param}' not found";
                    }
                }

                // Note security concerns
                echo "  🔒 Security concerns: " . implode(', ', $info['securityConcerns']) . "\n";
            } else {
                echo "\n❌ {$filename} - File not found\n";
                $this->failed++;
                $this->failures[] = "Missing critical endpoint: {$filename}";
            }
        }
    }

    private function testParameterSanitization(): void
    {
        echo "\n=== Parameter Sanitization Patterns ===\n";

        $sanitizationPatterns = [
            'filter_input(INPUT_POST' => 'PHP filter_input usage',
            'htmlspecialchars(' => 'HTML entity encoding',
            'strip_tags(' => 'HTML tag removal',
            'intval(' => 'Integer casting',
            'floatval(' => 'Float casting',
            'trim(' => 'Whitespace trimming',
            'json_decode(' => 'JSON parsing',
            'FILTER_VALIDATE_' => 'Input validation filters'
        ];

        $patternCounts = [];
        foreach ($this->ajaxFiles as $file) {
            $content = file_get_contents($file);
            $filename = basename($file);

            foreach ($sanitizationPatterns as $pattern => $description) {
                $count = substr_count($content, $pattern);
                if ($count > 0) {
                    $patternCounts[$description] = ($patternCounts[$description] ?? 0) + $count;
                    echo "  ✅ {$filename}: {$count}x {$description}\n";
                }
            }
        }

        echo "\n📊 Sanitization Summary:\n";
        foreach ($patternCounts as $description => $total) {
            echo "  • {$description}: {$total} usages across all endpoints\n";
        }
    }

    private function testSQLInjectionProtection(): void
    {
        echo "\n=== SQL Injection Protection Analysis ===\n";

        $riskyPatterns = [
            '/\$_POST.*\s*\.\s*["\'][^"\']*["\']/' => 'Direct $_POST concatenation in SQL',
            '/\$_GET.*\s*\.\s*["\'][^"\']*["\']/' => 'Direct $_GET concatenation in SQL',
            '/["\'][^"\']*\s*\.\s*\$_/' => 'Direct user input concatenation in SQL',
            '/mysql_query\s*\(/' => 'Legacy mysql_query usage',
            '/mysqli_query\s*\([^,]*\$[^,)]*[,)]/' => 'Direct variable in mysqli_query'
        ];

        $totalRisks = 0;
        foreach ($this->ajaxFiles as $file) {
            $content = file_get_contents($file);
            $filename = basename($file);

            foreach ($riskyPatterns as $pattern => $description) {
                if (preg_match($pattern, $content)) {
                    $totalRisks++;
                    $this->failures[] = "{$filename}: {$description}";
                    echo "  ❌ {$filename}: {$description}\n";
                }
            }
        }

        if ($totalRisks === 0) {
            echo "  ✅ No obvious SQL injection vulnerabilities found\n";
            $this->passed++;
        } else {
            echo "  ⚠️  {$totalRisks} potential SQL injection risks identified\n";
        }
    }

    private function outputResults(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "AJAX Endpoint Validation Results:\n";
        echo "Files checked: " . count($this->ajaxFiles) . "\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "Warnings: " . count($this->warnings) . "\n";

        if (!empty($this->failures)) {
            echo "\n🚨 Critical Issues:\n";
            foreach ($this->failures as $failure) {
                echo "  ❌ {$failure}\n";
            }
        }

        if (!empty($this->warnings)) {
            echo "\n⚠️  Warnings (should review):\n";
            foreach ($this->warnings as $warning) {
                echo "  ⚠️  {$warning}\n";
            }
        }

        if ($this->failed === 0) {
            echo "\n✅ AJAX endpoints appear secure and well-validated!\n";
        } else {
            echo "\n❌ Security and validation issues found in AJAX endpoints.\n";
        }

        echo "\n💡 Regular AJAX endpoint security review is recommended.\n";
    }
}

// Additional Test: Form Parameter Mapping Validation
class AjaxFormMappingValidator
{
    public static function validateFormToRepositoryMapping(): void
    {
        echo "\n=== AJAX Form-to-Repository Parameter Mapping ===\n";
        echo "Validating that AJAX endpoints correctly map form parameters to repository methods...\n\n";

        $mappingTests = [
            [
                'endpoint' => 'save_moment_from_realtime.php',
                'form_params' => ['frame_start', 'frame_end', 'notes', 'moment_date', 'perspectives'],
                'repository' => 'MomentRepository',
                'method' => 'insert',
                'description' => 'Moment creation from real-time form'
            ],
            [
                'endpoint' => 'update_moment_significance.php',
                'form_params' => ['moment_id', 'perspective_id', 'perspective_type', 'is_significant'],
                'repository' => 'MomentRepository',
                'method' => 'updateSignificance',
                'description' => 'Moment significance toggle'
            ],
            [
                'endpoint' => 'tokens.php',
                'form_params' => ['token_string', 'token_date', 'token_x_pos', 'token_y_pos'],
                'repository' => 'TokensRepository',
                'method' => 'update',
                'description' => 'Token position and content updates'
            ]
        ];

        foreach ($mappingTests as $test) {
            echo "🔗 {$test['endpoint']} -> {$test['repository']}::{$test['method']}\n";
            echo "   {$test['description']}\n";

            $endpointPath = __DIR__ . '/../wwwroot/admin/ajax/' . $test['endpoint'];
            if (file_exists($endpointPath)) {
                $content = file_get_contents($endpointPath);

                $foundParams = 0;
                foreach ($test['form_params'] as $param) {
                    if (strpos($content, $param) !== false) {
                        $foundParams++;
                        echo "   ✅ {$param}\n";
                    } else {
                        echo "   ❌ {$param} (missing)\n";
                    }
                }

                $coverage = round(($foundParams / count($test['form_params'])) * 100);
                echo "   📊 Parameter coverage: {$coverage}%\n\n";
            } else {
                echo "   ❌ Endpoint file not found\n\n";
            }
        }
    }
}

// Run all tests
$validator = new AjaxEndpointValidator();
$validator->run();

AjaxFormMappingValidator::validateFormToRepositoryMapping();

echo "🔐 AJAX endpoint security validation complete!\n";
echo "💡 Consider running this test after any AJAX endpoint changes.\n";
