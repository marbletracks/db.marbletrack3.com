<?php
/**
 * GET /api/v1/
 * Status check — confirms API key is valid.
 */
require_once __DIR__ . '/_auth.php';

/**
 * Set up by _auth.php.
 *
 * @var int $auth_user_id
 */

echo json_encode(['status' => 'ok', 'user_id' => $auth_user_id]);
