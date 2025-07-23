<?php
declare(strict_types=1);

// File: /wwwroot/admin/ajax/create_phrase_for_moment.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

use Database\PhrasesRepository;

$phrasesRepo = new PhrasesRepository($mla_database);

$token_ids = json_decode($_POST['token_ids'] ?? '[]');
$phrase_string = $_POST['phrase_string'] ?? '';
$moment_id = filter_input(INPUT_POST, 'moment_id', FILTER_VALIDATE_INT);

if (empty($token_ids) || empty($phrase_string) || !$moment_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters.']);
    exit;
}

try {
    $phrase_id = $phrasesRepo->create($phrase_string, $token_ids, $moment_id);

    // After creating the phrase, we need to make sure the worker is associated with the moment
    // The phrase holds the tokens, and tokens are associated with a worker.
    // This logic is a bit indirect. Let's assume for now the UI will handle reloading and showing the correct state.

    echo json_encode(['success' => true, 'phrase_id' => $phrase_id, 'moment_id' => $moment_id]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An internal error occurred: ' . $e->getMessage()]);
}
