<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

// Set JSON response header
header('Content-Type: application/json');

// Check authentication
if (!$is_logged_in->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['track_id'], $input['part_id'], $input['is_exclusive'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
        exit;
    }

    $track_id = (int) $input['track_id'];
    $part_id = (int) $input['part_id'];
    $is_exclusive = (bool) $input['is_exclusive'];

    if ($track_id <= 0 || $part_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid track or part ID']);
        exit;
    }

    $repo = new \Database\TrackRepository($mla_database);

    // Verify the track-part relationship exists
    if (!$repo->trackPartExists($track_id, $part_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Part is not assigned to this track']);
        exit;
    }

    // Update the exclusivity flag
    $repo->updateTrackPartExclusivity($track_id, $part_id, $is_exclusive);

    echo json_encode([
        'success' => true,
        'is_exclusive' => $is_exclusive
    ]);

} catch (Exception $e) {
    error_log("Track part exclusivity toggle error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
