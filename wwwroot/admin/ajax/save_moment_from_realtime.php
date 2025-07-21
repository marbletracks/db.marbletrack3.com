<?php
declare(strict_types=1);

// File: /wwwroot/admin/ajax/save_moment_from_realtime.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

use Database\MomentRepository;
use Database\PhrasesRepository;
use Database\MomentTranslationsRepository;

$momentRepo = new MomentRepository($mla_database);
$phrasesRepo = new PhrasesRepository($mla_database);
$translationsRepo = new MomentTranslationsRepository($mla_database);

// The `perspectives` array is expected in the format:
// perspectives[worker][123][note] = "Text"
// perspectives[worker][123][is_significant] = "1"
$perspectives = $_POST['perspectives'] ?? [];
$notes = $_POST['notes'] ?? '';
$frame_start = !empty($_POST['frame_start']) ? (int)$_POST['frame_start'] : null;
$frame_end = !empty($_POST['frame_end']) ? (int)$_POST['frame_end'] : null;
$moment_date = !empty($_POST['moment_date']) ? $_POST['moment_date'] : date('Y-m-d');
$token_ids = json_decode($_POST['token_ids'] ?? '[]');
$phrase_string = $_POST['phrase_string'] ?? '';

if (empty($notes) || empty($token_ids) || empty($phrase_string)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
    exit;
}

$mla_database->beginTransaction();

try {
    // 1. Create the moment
    $moment_id = $momentRepo->insert($frame_start, $frame_end, null, $notes, $moment_date);

    // 2. Create the phrase
    $phrasesRepo->create($phrase_string, $token_ids, $moment_id);

    // 3. Create moment translations from perspectives
    if (!empty($perspectives)) {
        foreach ($perspectives as $type => $entities) {
            foreach ($entities as $entity_id => $data) {
                $translation_note = $data['note'] ?? '';
                $is_significant = isset($data['is_significant']) && $data['is_significant'] == '1';

                if (!empty($translation_note)) {
                    $translationsRepo->create(
                        $moment_id,
                        (int)$entity_id,
                        $type, // 'worker' or 'part'
                        $translation_note,
                        $is_significant
                    );
                }
            }
        }
    }

    $mla_database->commit();
    echo json_encode(['success' => true, 'moment_id' => $moment_id]);

} catch (Exception $e) {
    $mla_database->rollBack();
    error_log('Error in save_moment_from_realtime.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'A server error occurred. Please check the logs.']);
}
