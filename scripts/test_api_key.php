<?php
/**
 * Tests for Auth\ApiKey class
 * Requires test database with api_keys table.
 *
 * Usage:
 *   php scripts/test_api_key.php
 *   php scripts/test_api_key.php --verbose
 */

require_once __DIR__ . '/../classes/Mlaphp/Autoloader.php';
$autoloader = new \Mlaphp\Autoloader();
spl_autoload_register(array($autoloader, 'load'));

$verbose = in_array('--verbose', $argv ?? []);

$passed = 0;
$failed = 0;
$failures = [];

function assert_test(bool $condition, string $message, int &$passed, int &$failed, array &$failures): void
{
    if ($condition) {
        $passed++;
        echo "  ✅ {$message}\n";
    } else {
        $failed++;
        $failures[] = $message;
        echo "  ❌ {$message}\n";
    }
}

echo "Testing Auth\\ApiKey...\n\n";

// Connect to test database
try {
    $config = new \Config();
    $db = \Database\Base::getTestDB($config);
    $db->connect();
} catch (\Exception $e) {
    echo "❌ Could not connect to test database: " . $e->getMessage() . "\n";
    echo "   Make sure test database exists and has api_keys table.\n";
    exit(1);
}

// Ensure api_keys table exists in test db
try {
    $db->fetchResults("SELECT 1 FROM api_keys LIMIT 1");
} catch (\Exception $e) {
    echo "❌ api_keys table not found in test database.\n";
    echo "   Run the migration: db_schemas/11_api_keys/create_api_keys.sql\n";
    exit(1);
}

// Ensure we have a test user
try {
    $results = $db->fetchResults("SELECT user_id FROM users LIMIT 1");
    if ($results->numRows() === 0) {
        echo "❌ No users in test database. Need at least one user.\n";
        exit(1);
    }
    $results->setRow(0);
    $test_user_id = (int) $results->data['user_id'];
} catch (\Exception $e) {
    echo "❌ Could not query users table: " . $e->getMessage() . "\n";
    exit(1);
}

$apiKey = new \Auth\ApiKey($db);

// Wrap all tests in a transaction so nothing persists
$db->beginTransaction();

// ── Test 1: Generate returns correct format ──────────────────────────────────
echo "Test 1: Key generation\n";

$raw_key = $apiKey->generateKey($test_user_id, 'test key');

assert_test(
    strlen($raw_key) === 64,
    "Generated key is 64 characters (got " . strlen($raw_key) . ")",
    $passed, $failed, $failures
);

assert_test(
    str_starts_with($raw_key, 'sk_'),
    "Generated key starts with 'sk_'",
    $passed, $failed, $failures
);

// ── Test 2: Validate accepts a fresh key ─────────────────────────────────────
echo "\nTest 2: Key validation\n";

$validated_user_id = $apiKey->validateKey($raw_key);

assert_test(
    $validated_user_id === $test_user_id,
    "validateKey returns correct user_id ({$test_user_id})",
    $passed, $failed, $failures
);

$key_id = $apiKey->getLastKeyId();

assert_test(
    $key_id !== null && $key_id > 0,
    "getLastKeyId returns a positive integer ({$key_id})",
    $passed, $failed, $failures
);

// ── Test 3: Validate rejects garbage ─────────────────────────────────────────
echo "\nTest 3: Reject invalid keys\n";

$bad_result = $apiKey->validateKey('sk_this_is_definitely_not_a_real_key_and_should_fail_00000000');

assert_test(
    $bad_result === null,
    "validateKey returns null for garbage key",
    $passed, $failed, $failures
);

$empty_result = $apiKey->validateKey('');

assert_test(
    $empty_result === null,
    "validateKey returns null for empty string",
    $passed, $failed, $failures
);

// ── Test 4: Revoke makes key fail validation ─────────────────────────────────
echo "\nTest 4: Key revocation\n";

$revoked = $apiKey->revokeKey($key_id, $test_user_id);

assert_test(
    $revoked === true,
    "revokeKey returns true",
    $passed, $failed, $failures
);

$after_revoke = $apiKey->validateKey($raw_key);

assert_test(
    $after_revoke === null,
    "validateKey returns null after revocation",
    $passed, $failed, $failures
);

// ── Test 5: Revoke with wrong user_id fails ──────────────────────────────────
echo "\nTest 5: Revoke ownership check\n";

$raw_key_2 = $apiKey->generateKey($test_user_id, 'test key 2');
$apiKey->validateKey($raw_key_2);
$key_id_2 = $apiKey->getLastKeyId();

$wrong_user_revoke = $apiKey->revokeKey($key_id_2, 999999);

assert_test(
    $wrong_user_revoke === false,
    "revokeKey returns false for wrong user_id",
    $passed, $failed, $failures
);

$still_valid = $apiKey->validateKey($raw_key_2);

assert_test(
    $still_valid === $test_user_id,
    "Key still works after failed revoke attempt",
    $passed, $failed, $failures
);

// ── Test 6: getKeysForUser ───────────────────────────────────────────────────
echo "\nTest 6: List keys\n";

$keys = $apiKey->getKeysForUser($test_user_id);

assert_test(
    is_array($keys) && count($keys) >= 2,
    "getKeysForUser returns at least 2 keys (got " . count($keys) . ")",
    $passed, $failed, $failures
);

// ── Cleanup: roll back all test data ─────────────────────────────────────────
$db->rollBack();

// ── Results ──────────────────────────────────────────────────────────────────
echo "\n" . str_repeat("=", 50) . "\n";
echo "ApiKey Test Results:\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";

if (!empty($failures)) {
    echo "\nFailures:\n";
    foreach ($failures as $f) {
        echo "  ❌ {$f}\n";
    }
}

exit($failed > 0 ? 1 : 0);
