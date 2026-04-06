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

    if (!$input || !isset($input['from_track_id'], $input['to_track_id'], $input['marble_sizes'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
        exit;
    }

    $from_track_id = (int) $input['from_track_id'];
    $to_track_id = (int) $input['to_track_id'];
    $marble_sizes = $input['marble_sizes'];
    $connection_type = $input['connection_type'] ?? 'direct';
    $description = $input['description'] ?? '';

    if ($from_track_id <= 0 || $to_track_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid track IDs']);
        exit;
    }

    if ($from_track_id === $to_track_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Cannot connect a track to itself']);
        exit;
    }

    if (empty($marble_sizes) || !is_array($marble_sizes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'At least one marble size must be selected']);
        exit;
    }

    // Validate marble sizes
    $valid_sizes = ['small', 'medium', 'large'];
    foreach ($marble_sizes as $size) {
        if (!in_array($size, $valid_sizes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid marble size: ' . $size]);
            exit;
        }
    }

    $repo = new \Database\TrackRepository($mla_database);

    // Check if connection already exists
    if ($repo->connectionExists($from_track_id, $to_track_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Connection already exists between these tracks']);
        exit;
    }

    // Insert the connection
    $connection_id = $repo->insertConnection($from_track_id, $to_track_id, $marble_sizes, $connection_type, $description);

    // Get track info for response
    $from_track = $repo->findById($from_track_id);
    $to_track = $repo->findById($to_track_id);

    if (!$from_track || !$to_track) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'One or both tracks not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'connection_id' => $connection_id,
        'from_track' => [
            'id' => $from_track->track_id,
            'name' => $from_track->track_name
        ],
        'to_track' => [
            'id' => $to_track->track_id,
            'name' => $to_track->track_name
        ],
        'marble_sizes' => $marble_sizes,
        'marble_sizes_display' => implode(', ', $marble_sizes)
    ]);

} catch (Exception $e) {
    error_log("Track connection creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
