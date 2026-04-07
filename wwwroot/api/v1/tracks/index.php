<?php
/**
 * GET   /api/v1/tracks       — list all tracks
 * GET   /api/v1/tracks/6     — single track by ID with component parts
 * PATCH /api/v1/tracks/6     — update track descriptions
 */
require_once __DIR__ . '/../_auth.php';

$repo = new \Database\TrackRepository($mla_database);
$method = $_SERVER['REQUEST_METHOD'];

$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$sub = trim(preg_replace('#^/api/v1/tracks#', '', $uri_path), '/');

// ── PATCH /api/v1/tracks/{id} — update track ────────────────────────────────
if ($method === 'PATCH' && $sub !== '') {
    require_write();

    if (!ctype_digit($sub)) {
        http_response_code(400);
        echo json_encode(['error' => 'Track ID must be numeric']);
        exit;
    }

    $track = $repo->findById((int) $sub);
    if (!$track) {
        http_response_code(404);
        echo json_encode(['error' => 'Track not found', 'track_id' => (int) $sub]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        exit;
    }

    if (array_key_exists('visitor_description', $input)) {
        $mla_database->executeSQL(
            "UPDATE tracks SET visitor_description = ? WHERE track_id = ?",
            'si', [$input['visitor_description'], $track->track_id]
        );
    }
    if (array_key_exists('technical_description', $input)) {
        $mla_database->executeSQL(
            "UPDATE tracks SET technical_description = ? WHERE track_id = ?",
            'si', [$input['technical_description'], $track->track_id]
        );
    }

    $updated = $repo->findById($track->track_id);
    echo json_encode($updated);
    exit;
}

// ── GET /api/v1/tracks — list ───────────────────────────────────────────────
if ($method === 'GET' && $sub === '') {
    $tracks = $repo->findAll();

    $output = [];
    foreach ($tracks as $track) {
        $output[] = [
            'track_id'              => $track->track_id,
            'track_alias'           => $track->track_alias,
            'track_name'            => $track->track_name,
            'technical_description' => $track->technical_description,
            'visitor_description'   => $track->visitor_description,
            'marble_sizes_accepted' => $track->marble_sizes_accepted,
            'is_transport'          => (bool) $track->is_transport,
            'is_splitter'           => (bool) $track->is_splitter,
            'is_landing_zone'       => (bool) $track->is_landing_zone,
            'entity_type'           => $track->entity_type,
        ];
    }

    echo json_encode(['tracks' => $output, 'total' => count($output)]);
    exit;
}

// ── GET /api/v1/tracks/{id} — single track ──────────────────────────────────
if ($method === 'GET' && $sub !== '') {
    if (!ctype_digit($sub)) {
        http_response_code(400);
        echo json_encode(['error' => 'Track ID must be numeric']);
        exit;
    }

    $track = $repo->findById((int) $sub);

    if (!$track) {
        http_response_code(404);
        echo json_encode(['error' => 'Track not found', 'track_id' => (int) $sub]);
        exit;
    }

    echo json_encode($track);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
