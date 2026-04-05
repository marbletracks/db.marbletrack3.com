<?php
/**
 * Shared API authentication. Include at the top of every API endpoint.
 *
 * After inclusion, these variables are available:
 *   $auth_user_id   — authenticated user's ID
 *   $auth_key_id    — the API key's ID (for usage logging)
 *   $auth_can_write — whether this key has write permission
 *   $mla_database   — DbInterface instance (from prepend.php)
 *
 * Call require_write() at the top of any write operation.
 */

header('Content-Type: application/json');

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

$raw_key = $_SERVER['HTTP_X_API_KEY'] ?? null;
if (empty($raw_key)) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing X-API-Key header']);
    exit;
}

$apiKeyAuth = new \Auth\ApiKey($mla_database);
$auth_user_id = $apiKeyAuth->validateKey($raw_key);
$auth_key_id  = $apiKeyAuth->getLastKeyId();

if (!$auth_user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or revoked API key']);
    exit;
}

$auth_can_write = $apiKeyAuth->canWrite();

/**
 * Call at the top of any write operation (PATCH, POST, DELETE).
 * Returns 403 if the key is read-only.
 */
function require_write(): void
{
    global $auth_can_write;
    if (!$auth_can_write) {
        http_response_code(403);
        echo json_encode(['error' => 'This API key does not have write permission']);
        exit;
    }
}
