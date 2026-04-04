<?php
/**
 * Shared API authentication. Include at the top of every API endpoint.
 *
 * After inclusion, these variables are available:
 *   $auth_user_id  — authenticated user's ID
 *   $auth_key_id   — the API key's ID (for usage logging)
 *   $mla_database  — DbInterface instance (from prepend.php)
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
