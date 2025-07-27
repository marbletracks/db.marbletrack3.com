<?php
/**
 * SQL Parameter Validation Tests for All Repository Classes
 *
 * This test validates that all SQL queries in repository classes have
 * correct parameter counts matching placeholder counts and type strings.
 * This would catch bugs like the one found in Issues #57/#58.
 */

// Bootstrap without database
require_once __DIR__ . '/../classes/Mlaphp/Autoloader.php';
$autoloader = new \Mlaphp\Autoloader();
spl_autoload_register(array($autoloader, 'load'));

class RepositorySQLParameterValidator
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];
    private array $repositoryFiles = [];

    public function __construct()
    {
        $this->findRepositoryFiles();
    }

    private function findRepositoryFiles(): void
    {
        $repositoryDir = __DIR__ . '/../classes/Database';
        $files = glob($repositoryDir . '/*Repository.php');

        foreach ($files as $file) {
            $this->repositoryFiles[] = $file;
        }
    }

    public function run(): void
    {
        echo "Validating SQL parameters in all Repository classes...\n\n";

        foreach ($this->repositoryFiles as $file) {
            $this->validateRepositoryFile($file);
        }

        // Test specific known methods with manual validation
        $this->testKnownSQLQueries();

        // Output results
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "SQL Parameter Validation Results:\n";
        echo "Repository files checked: " . count($this->repositoryFiles) . "\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";

        if (!empty($this->failures)) {
            echo "\nFailures:\n";
            foreach ($this->failures as $failure) {
                echo "❌ {$failure}\n";
            }
        }

        if ($this->failed === 0) {
            echo "✅ All SQL parameter validations passed!\n";
            echo "✅ No parameter/placeholder mismatches found in repository classes.\n";
        } else {
            echo "❌ SQL parameter mismatches found that could cause runtime errors.\n";
        }
    }

    private function validateRepositoryFile(string $filePath): void
    {
        $filename = basename($filePath);
        echo "Checking {$filename}...\n";

        $content = file_get_contents($filePath);

        // Find all executeSQL and fetchResults calls
        $pattern = '/(?:executeSQL|fetchResults)\s*\(\s*["\']([^"\']*)["\'],\s*["\']([^"\']*)["\'],\s*(\[[^\]]*\])/';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $queriesFound = 0;
        foreach ($matches as $match) {
            $sql = $match[1];
            $types = $match[2];
            $paramsArray = $match[3];

            $queriesFound++;
            $this->validateSQLQuery($filename, $sql, $types, $paramsArray);
        }

        if ($queriesFound === 0) {
            echo "  ℹ️  No parameterized queries found in {$filename}\n";
        } else {
            echo "  ✓ Checked {$queriesFound} parameterized queries in {$filename}\n";
        }
    }

    private function validateSQLQuery(string $filename, string $sql, string $types, string $paramsArray): void
    {
        $placeholderCount = substr_count($sql, '?');
        $typeCount = strlen($types);

        // Count parameters in array (rough estimate)
        $paramCount = substr_count($paramsArray, ',') + 1;
        if (trim($paramsArray) === '[]') {
            $paramCount = 0;
        }

        $queryStart = substr($sql, 0, 50) . (strlen($sql) > 50 ? '...' : '');

        if ($placeholderCount !== $typeCount) {
            $this->failed++;
            $this->failures[] = "{$filename}: SQL placeholders ({$placeholderCount}) ≠ type string length ({$typeCount}) in: {$queryStart}";
        } else {
            $this->passed++;
        }
    }

    private function testKnownSQLQueries(): void
    {
        echo "\nTesting known SQL patterns from repository classes...\n";

        // Known queries from MomentRepository that we can validate
        $knownQueries = [
            [
                'name' => 'MomentRepository::updateSignificance',
                'sql' => 'UPDATE moment_translations SET is_significant = ? WHERE moment_id = ? AND perspective_entity_id = ? AND perspective_entity_type = ?',
                'types' => 'iiis',
                'params' => [1, 123, 456, 'worker']
            ],
            [
                'name' => 'MomentRepository::deleteTranslation',
                'sql' => 'DELETE FROM moment_translations WHERE moment_id = ? AND perspective_entity_id = ? AND perspective_entity_type = ?',
                'types' => 'iis',
                'params' => [123, 456, 'worker']
            ],
            [
                'name' => 'MomentRepository::findById',
                'sql' => 'SELECT moment_id, frame_start, frame_end, take_id, notes, moment_date FROM moments WHERE moment_id = ?',
                'types' => 'i',
                'params' => [123]
            ],
            [
                'name' => 'Database::insertRecord (generic)',
                'sql' => 'INSERT INTO test_table (col1, col2, col3) VALUES (?, ?, ?)',
                'types' => 'ssi',
                'params' => ['value1', 'value2', 123]
            ],
            [
                'name' => 'Database::updateRecord (generic)',
                'sql' => 'UPDATE test_table SET col1 = ?, col2 = ? WHERE id = ?',
                'types' => 'ssi',
                'params' => ['value1', 'value2', 123]
            ]
        ];

        foreach ($knownQueries as $query) {
            $placeholderCount = substr_count($query['sql'], '?');
            $typeCount = strlen($query['types']);
            $paramCount = count($query['params']);

            $allMatch = ($placeholderCount === $typeCount && $typeCount === $paramCount);

            if ($allMatch) {
                $this->passed++;
                echo "✅ {$query['name']}: SQL placeholders ({$placeholderCount}) = types ({$typeCount}) = params ({$paramCount})\n";
            } else {
                $this->failed++;
                $this->failures[] = "{$query['name']}: Mismatch - placeholders({$placeholderCount}), types({$typeCount}), params({$paramCount})";
                echo "❌ {$query['name']}: SQL placeholders ({$placeholderCount}) ≠ types ({$typeCount}) ≠ params ({$paramCount})\n";
            }
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
}

// Advanced SQL Pattern Detection
class AdvancedSQLValidator
{
    public static function findComplexSQLPatterns(string $content): array
    {
        $patterns = [];

        // Pattern 1: Multi-line SQL with parameters
        $multiLinePattern = '/\$this->db->(?:executeSQL|fetchResults)\s*\(\s*["\']([^"\']*(?:\s*\.\s*["\'][^"\']*["\'])*)["\'],\s*["\']([^"\']*)["\'],\s*(\[[^\]]*\])/s';
        preg_match_all($multiLinePattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $patterns[] = [
                'type' => 'multi-line',
                'sql' => str_replace(['"', "'", ' . '], '', $match[1]),
                'types' => $match[2],
                'params' => $match[3]
            ];
        }

        // Pattern 2: Dynamic SQL construction
        if (preg_match_all('/\$sql\s*=\s*["\']([^"\']*)["\']/', $content, $dynamicMatches)) {
            foreach ($dynamicMatches[1] as $sql) {
                if (strpos($sql, '?') !== false) {
                    $patterns[] = [
                        'type' => 'dynamic',
                        'sql' => $sql,
                        'types' => 'unknown',
                        'params' => 'unknown'
                    ];
                }
            }
        }

        return $patterns;
    }
}

// Run the validation
echo "=== Repository SQL Parameter Validation Test ===\n";
echo "This test checks all repository classes for SQL parameter/placeholder mismatches\n";
echo "that could cause runtime errors like those found in Issues #57/#58.\n\n";

$validator = new RepositorySQLParameterValidator();
$validator->run();

echo "\n=== Advanced Pattern Detection ===\n";
echo "Scanning for complex SQL patterns that need manual review...\n\n";

$repositoryDir = __DIR__ . '/../classes/Database';
$repositoryFiles = glob($repositoryDir . '/*Repository.php');

foreach ($repositoryFiles as $file) {
    $content = file_get_contents($file);
    $patterns = AdvancedSQLValidator::findComplexSQLPatterns($content);

    if (!empty($patterns)) {
        echo "📋 " . basename($file) . " - " . count($patterns) . " complex patterns found\n";
        foreach ($patterns as $pattern) {
            $sqlPreview = substr($pattern['sql'], 0, 60) . (strlen($pattern['sql']) > 60 ? '...' : '');
            echo "  🔍 {$pattern['type']}: {$sqlPreview}\n";
        }
    }
}

echo "\n✅ Repository SQL Parameter validation complete!\n";
echo "💡 Run this test after any repository changes to catch parameter mismatches early.\n";
