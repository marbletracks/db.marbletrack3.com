<?php
declare(strict_types=1);

// File: /wwwroot/admin/ajax/search_moments.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

$request = new RobRequest();

if (!$is_logged_in->isLoggedIn()) {
    $request->jsonError('Unauthorized', 401);
}

header('Content-Type: application/json');

use Database\MomentRepository;

$momentRepo = new MomentRepository($mla_database);

$worker_id = $request->getInt('worker_id');
$frame_start = $request->getInt('frame_start');
$frame_end = $request->getInt('frame_end');

if (!$worker_id || !$frame_start || !$frame_end) {
    $request->jsonError('Missing or invalid parameters.', 400);
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

    $request->jsonSuccess(['moments' => $response]);

} catch (Exception $e) {
    $request->jsonError('An internal error occurred: ' . $e->getMessage(), 500);
}
