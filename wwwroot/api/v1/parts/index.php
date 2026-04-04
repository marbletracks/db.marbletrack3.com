<?php
/**
 * GET /api/v1/parts          — list all parts
 * GET /api/v1/parts?search=  — search by name or alias
 * GET /api/v1/parts/42       — single part by ID
 * GET /api/v1/parts/5poss    — single part by alias
 */
require_once __DIR__ . '/../_auth.php';

$repo = new \Database\PartsRepository($mla_database, 'en');
$trackRepo = new \Database\TrackRepository($mla_database);

// Parse sub-path: /api/v1/parts/42 → "42"
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$sub = trim(preg_replace('#^/api/v1/parts#', '', $uri_path), '/');

if ($sub === '') {
    // List or search
    $search = trim($_GET['search'] ?? '');
    if ($search !== '') {
        $parts = $repo->findByFilter($search);
    } else {
        $parts = $repo->findAll();
    }

    $output = [];
    foreach ($parts as $part) {
        $tracks = $trackRepo->findTracksByPartId($part->part_id);
        $output[] = partToArray($part, $tracks);
    }

    echo json_encode(['parts' => $output, 'total' => count($output)]);
} else {
    // Single part by ID or alias
    if (ctype_digit($sub)) {
        $part = $repo->findById((int) $sub);
    } else {
        $part = $repo->findByAlias($sub);
    }

    if (!$part) {
        http_response_code(404);
        echo json_encode(['error' => 'Part not found', 'query' => $sub]);
        exit;
    }

    $tracks = $trackRepo->findTracksByPartId($part->part_id);

    echo json_encode(partToArray($part, $tracks, true));
}

function partToArray(\Physical\Part $part, array $tracks, bool $detail = false): array
{
    $data = [
        'part_id'    => $part->part_id,
        'part_alias' => $part->part_alias,
        'name'       => $part->name,
        'has_description' => ($part->description !== '' && $part->description !== $part->name),
        'photo_count'     => count($part->photos),
        'moment_count'    => count($part->moments),
        'tracks' => array_map(function ($t) {
            return [
                'track_id'   => $t['track_id'] ?? $t->track_id ?? null,
                'track_name' => $t['track_name'] ?? $t->track_name ?? null,
                'part_role'  => $t['part_role'] ?? null,
                'is_exclusive' => (bool) ($t['is_exclusive_to_track'] ?? false),
            ];
        }, $tracks),
    ];

    if ($detail) {
        $data['description'] = $part->description;
        $data['is_rail']     = $part->is_rail;
        $data['is_support']  = $part->is_support;
        $data['is_track']    = $part->is_track;
        $data['photos']      = array_map(function ($photo) {
            return [
                'photo_id' => $photo->photo_id,
                'url'      => $photo->url,
            ];
        }, $part->photos);
        $data['moments'] = array_map(function ($moment) {
            return [
                'moment_id'   => $moment->moment_id,
                'moment_date' => $moment->moment_date ?? null,
                'frame_start' => $moment->frame_start ?? null,
                'frame_end'   => $moment->frame_end ?? null,
                'notes'       => $moment->notes ?? null,
            ];
        }, $part->moments);
    }

    return $data;
}
