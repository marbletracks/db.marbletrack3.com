<?php
/**
 * GET /api/v1/tracks     — list all tracks
 * GET /api/v1/tracks/6   — single track by ID with component parts
 */
require_once __DIR__ . '/../_auth.php';

$repo = new \Database\TrackRepository($mla_database);

$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$sub = trim(preg_replace('#^/api/v1/tracks#', '', $uri_path), '/');

if ($sub === '') {
    $tracks = $repo->findAll();

    $output = [];
    foreach ($tracks as $track) {
        $output[] = [
            'track_id'              => $track->track_id,
            'track_alias'           => $track->track_alias,
            'track_name'            => $track->track_name,
            'technical_description'  => $track->technical_description,
            'visitor_description'   => $track->visitor_description,
            'marble_sizes_accepted' => $track->marble_sizes_accepted,
            'is_transport'          => (bool) $track->is_transport,
            'is_splitter'           => (bool) $track->is_splitter,
            'is_landing_zone'       => (bool) $track->is_landing_zone,
            'entity_type'           => $track->entity_type,
        ];
    }

    echo json_encode(['tracks' => $output, 'total' => count($output)]);
} else {
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
}
