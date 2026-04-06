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

    if (!$input || !isset($input['from_track_id'], $input['to_track_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
        exit;
    }

    $from_track_id = (int) $input['from_track_id'];
    $to_track_id = (int) $input['to_track_id'];

    if ($from_track_id <= 0 || $to_track_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid track IDs']);
        exit;
    }

    // Delete the connection
    $repo = new \Database\TrackRepository($mla_database);
    $repo->deleteConnection($from_track_id, $to_track_id);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Track connection deletion error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
