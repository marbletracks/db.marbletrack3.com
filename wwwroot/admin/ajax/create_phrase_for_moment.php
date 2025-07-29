<?php
declare(strict_types=1);

// File: /wwwroot/admin/ajax/create_phrase_for_moment.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

$request = new RobRequest();

if (!$is_logged_in->isLoggedIn()) {
    $request->jsonError('Unauthorized', 401);
}

header('Content-Type: application/json');

use Database\PhrasesRepository;

$phrasesRepo = new PhrasesRepository($mla_database);

$token_ids = json_decode($request->getString('token_ids', '[]'));
$phrase_string = $request->getString('phrase_string');
$moment_id = $request->getInt('moment_id');

if (empty($token_ids) || empty($phrase_string) || !$moment_id) {
    $request->jsonError('Missing required parameters.', 400);
}

try {
    $phrase_id = $phrasesRepo->create($phrase_string, $token_ids, $moment_id);

    // After creating the phrase, we need to make sure the worker is associated with the moment
    // The phrase holds the tokens, and tokens are associated with a worker.
    // This logic is a bit indirect. Let's assume for now the UI will handle reloading and showing the correct state.

    $request->jsonSuccess(['phrase_id' => $phrase_id, 'moment_id' => $moment_id]);

} catch (Exception $e) {
    $request->jsonError('An internal error occurred: ' . $e->getMessage(), 500);
}
