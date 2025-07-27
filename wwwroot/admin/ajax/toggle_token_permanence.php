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
$request = new RobRequest();

$token_id = $request->getInt('token_id');

$request->requireFields([
    'token_id' => $token_id > 0
]);

try {
    $new_status = $tokensRepo->togglePermanence($token_id);
    $request->jsonSuccess(['is_permanent' => $new_status]);
} catch (Exception $e) {
    $request->jsonError($e->getMessage(), 500);
}
