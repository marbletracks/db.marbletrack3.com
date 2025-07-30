<?php
declare(strict_types=1);

// File: /wwwroot/admin/ajax/save_moment_from_realtime.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

$request = new RobRequest();

if (!$is_logged_in->isLoggedIn()) {
    $request->jsonError('Unauthorized', 401);
}

header('Content-Type: application/json');

use Database\MomentRepository;
use Database\PhrasesRepository;
use Database\WorkersRepository;
use Database\PartsRepository;

$momentRepo = new MomentRepository($mla_database);
$phrasesRepo = new PhrasesRepository($mla_database);
$workersRepo = new WorkersRepository($mla_database, 'en');
$partsRepo = new PartsRepository($mla_database, 'en');


// The `perspectives` array is expected in the format:
// perspectives[worker][123][note] = "Text"
// perspectives[worker][123][is_significant] = "1"
$perspectives = $_POST['perspectives'] ?? [];
$notes = $request->getString('notes');
$frame_start = $request->getInt('frame_start');
$frame_end = $request->getInt('frame_end');
$take_id = $request->getInt('take_id');
$moment_date = $request->getString('moment_date', date('Y-m-d'));
$token_ids = json_decode($request->getString('token_ids', '[]'));
$phrase_string = $request->getString('phrase_string');

if (empty($notes) || empty($token_ids) || empty($phrase_string)) {
    $request->jsonError('Missing required fields.', 400);
}

$mla_database->beginTransaction();

try {
    // 1. Create the moment
    $moment_id = $momentRepo->insert($frame_start, $frame_end, $take_id, $notes, $moment_date);

    // 2. Create the phrase
    $phrasesRepo->create($phrase_string, $token_ids, $moment_id);

    // 3. Save the translations
    $momentRepo->saveTranslations($moment_id, $perspectives);

    $mla_database->commit();
    $request->jsonSuccess(['moment_id' => $moment_id]);

} catch (Exception $e) {
    $mla_database->rollBack();
    error_log('Error in save_moment_from_realtime.php: ' . $e->getMessage());
    $request->jsonError('A server error occurred. Please check the logs.', 500);
}