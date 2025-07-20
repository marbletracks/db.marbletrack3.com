<?php
declare(strict_types=1);

// File: /wwwroot/admin/ajax/toggle_token_permanence.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

use Database\TokensRepository;

$tokensRepo = new TokensRepository($mla_database);

$token_id = (int) ($_POST['token_id'] ?? 0);

if ($token_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid Token ID']);
    exit;
}

try {
    $new_status = $tokensRepo->togglePermanence($token_id);
    echo json_encode(['success' => true, 'is_permanent' => $new_status]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
