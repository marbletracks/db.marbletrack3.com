<?php
declare(strict_types=1);

// File: /wwwroot/admin/ajax/create_moment_from_tokens.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

use Database\TokensRepository;
use Database\PhrasesRepository;
use Database\MomentRepository;
use Database\WorkersRepository;
use Database\PartsRepository;

$tokensRepo = new TokensRepository($mla_database);
$phrasesRepo = new PhrasesRepository($mla_database);
$momentRepo = new MomentRepository($mla_database);
$workersRepo = new WorkersRepository($mla_database, 'en');
$partsRepo = new PartsRepository($mla_database, 'en');


$action = $_POST['action'] ?? '';

if ($action !== 'create_from_tokens') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

try {
    $token_ids = json_decode($_POST['token_ids'] ?? '[]');
    if (empty($token_ids)) {
        throw new Exception('No token IDs provided');
    }

    $tokens = [];
    foreach ($token_ids as $token_id) {
        $token = $tokensRepo->findById((int)$token_id);
        if ($token) {
            $tokens[] = $token;
        }
    }

    if (empty($tokens)) {
        throw new Exception('No valid tokens found for the given IDs');
    }

    // Create the phrase string without alias expansion
    $phrase_string = implode(' ', array_map(fn($t) => $t->token_string, $tokens));

    // Create the phrase in the database
    $phrase_id = $phrasesRepo->create($phrase_string, $token_ids);

    // Expand aliases for the moment notes
    $expanded_with_workers = $workersRepo->expandShortcodesForBackend($phrase_string, "worker", 'en');
    $moment_notes = $partsRepo->expandShortcodesForBackend($expanded_with_workers, "part", 'en');

    // 1. Create the moment first to get a valid moment_id
    $moment_id = $momentRepo->insert($frame_start, $frame_end, null, $moment_notes, $moment_date);

    // 2. Now create the phrase, linking it to the new moment
    $phrase_id = $phrasesRepo->create($phrase_string, $token_ids, $moment_id);

    echo json_encode(['success' => true, 'moment_id' => $moment_id, 'phrase_id' => $phrase_id]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
