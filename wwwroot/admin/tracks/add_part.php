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

    if (!$input || !isset($input['track_id'], $input['part_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
        exit;
    }

    $track_id = (int) $input['track_id'];
    $part_id = (int) $input['part_id'];
    $part_role = $input['part_role'] ?? 'main';

    if ($track_id <= 0 || $part_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid track or part ID']);
        exit;
    }

    // Validate part role
    $valid_roles = ['main', 'rail', 'support', 'connector', 'guide'];
    if (!in_array($part_role, $valid_roles)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid part role: ' . $part_role]);
        exit;
    }

    $trackRepo = new \Database\TrackRepository($mla_database);
    $partsRepo = new \Database\PartsRepository($mla_database, 'en');

    // Check if part is already in this track
    if ($trackRepo->trackPartExists($track_id, $part_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Part is already assigned to this track']);
        exit;
    }

    // Add the part to the track
    $track_part_id = $trackRepo->insertTrackPart($track_id, $part_id, $part_role);

    // Get part info for response
    $part = $partsRepo->findById($part_id);

    if (!$part) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Part not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'track_part_id' => $track_part_id,
        'part' => [
            'id' => $part->part_id,
            'name' => $part->name ?: $part->part_alias,
            'role' => $part_role
        ]
    ]);

} catch (Exception $e) {
    error_log("Track part creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
