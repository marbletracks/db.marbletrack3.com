<?php
/**
 * GET /api/v1/rides              — list all rides
 * GET /api/v1/rides/grand_spiral — single ride by alias with track sequence
 * GET /api/v1/rides/1            — single ride by ID with track sequence
 */
require_once __DIR__ . '/../_auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$sub = trim(preg_replace('#^/api/v1/rides#', '', $uri_path), '/');

// ── GET /api/v1/rides — list ────────────────────────────────────────────────
if ($method === 'GET' && $sub === '') {
    $results = $mla_database->fetchResults(
        "SELECT r.ride_id, r.ride_alias, r.ride_name, r.ride_tagline, r.marble_size, r.is_complete,
                COUNT(rt.track_id) as track_count
         FROM rides r
         LEFT JOIN ride_tracks rt ON r.ride_id = rt.ride_id
         GROUP BY r.ride_id
         ORDER BY r.ride_id ASC"
    );

    $output = [];
    for ($i = 0; $i < $results->numRows(); $i++) {
        $results->setRow($i);
        $row = $results->data;
        $output[] = [
            'ride_id'     => (int) $row['ride_id'],
            'ride_alias'  => $row['ride_alias'],
            'ride_name'   => $row['ride_name'],
            'ride_tagline' => $row['ride_tagline'],
            'marble_size' => $row['marble_size'],
            'is_complete' => (bool) $row['is_complete'],
            'track_count' => (int) $row['track_count'],
        ];
    }

    echo json_encode(['rides' => $output, 'total' => count($output)]);
    exit;
}

// ── GET /api/v1/rides/{id or alias} — single ride with track sequence ───────
if ($method === 'GET' && $sub !== '') {
    if (ctype_digit($sub)) {
        $results = $mla_database->fetchResults(
            "SELECT * FROM rides WHERE ride_id = ?", 'i', [(int) $sub]
        );
    } else {
        $results = $mla_database->fetchResults(
            "SELECT * FROM rides WHERE ride_alias = ?", 's', [$sub]
        );
    }

    if ($results->numRows() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Ride not found', 'query' => $sub]);
        exit;
    }

    $results->setRow(0);
    $ride = $results->data;
    $ride_id = (int) $ride['ride_id'];

    // Get track sequence
    $trackResults = $mla_database->fetchResults(
        "SELECT rt.sequence_order, rt.experience_note,
                t.track_id, t.track_alias, t.track_name, t.technical_description, t.visitor_description,
                t.marble_sizes_accepted
         FROM ride_tracks rt
         JOIN tracks t ON rt.track_id = t.track_id
         WHERE rt.ride_id = ?
         ORDER BY rt.sequence_order ASC",
        'i', [$ride_id]
    );

    $tracks = [];
    for ($i = 0; $i < $trackResults->numRows(); $i++) {
        $trackResults->setRow($i);
        $t = $trackResults->data;
        $tracks[] = [
            'sequence_order'  => (int) $t['sequence_order'],
            'track_id'        => (int) $t['track_id'],
            'track_alias'     => $t['track_alias'],
            'track_name'      => $t['track_name'],
            'experience_note' => $t['experience_note'],
        ];
    }

    echo json_encode([
        'ride_id'          => $ride_id,
        'ride_alias'       => $ride['ride_alias'],
        'ride_name'        => $ride['ride_name'],
        'ride_description' => $ride['ride_description'],
        'ride_tagline'     => $ride['ride_tagline'],
        'marble_size'      => $ride['marble_size'],
        'is_complete'      => (bool) $ride['is_complete'],
        'tracks'           => $tracks,
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
