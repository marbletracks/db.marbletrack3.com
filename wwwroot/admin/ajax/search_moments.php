<?php
declare(strict_types=1);

// File: /wwwroot/admin/ajax/search_moments.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

use Database\MomentRepository;

$momentRepo = new MomentRepository($mla_database);

$worker_id = filter_input(INPUT_POST, 'worker_id', FILTER_VALIDATE_INT);
$frame_start = filter_input(INPUT_POST, 'frame_start', FILTER_VALIDATE_INT);
$frame_end = filter_input(INPUT_POST, 'frame_end', FILTER_VALIDATE_INT);

if (!$worker_id || $frame_start === false || $frame_end === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid parameters.']);
    exit;
}

try {
    $similar_moments = $momentRepo->findSimilarMoments($worker_id, $frame_start, $frame_end);

    $response = [];
    foreach ($similar_moments as $moment) {
        $response[] = [
            'moment_id' => $moment->moment_id,
            'frame_start' => $moment->frame_start,
            'frame_end' => $moment->frame_end,
            'notes' => $moment->notes,
        ];
    }

    echo json_encode(['success' => true, 'moments' => $response]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An internal error occurred: ' . $e->getMessage()]);
}
