<?php
/**
 * GET  /api/v1/parts          — list all parts
 * GET  /api/v1/parts?search=  — search by name or alias
 * GET  /api/v1/parts/42       — single part by ID
 * GET  /api/v1/parts/5poss    — single part by alias
 * PATCH /api/v1/parts/42      — update description (and optionally name)
 * POST /api/v1/parts/42/tracks — assign part to a track
 */
require_once __DIR__ . '/../_auth.php';

$repo = new \Database\PartsRepository($mla_database, 'en');
$trackRepo = new \Database\TrackRepository($mla_database);

$method = $_SERVER['REQUEST_METHOD'];
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$sub = trim(preg_replace('#^/api/v1/parts#', '', $uri_path), '/');

// ── PATCH /api/v1/parts/{id or alias} — update part description ──────────────
if ($method === 'PATCH' && $sub !== '') {
    require_write();
    $part = findPart($repo, $sub);
    if (!$part) {
        http_response_code(404);
        echo json_encode(['error' => 'Part not found', 'query' => $sub]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        exit;
    }

    $name = $input['name'] ?? $part->name;
    $description = $input['description'] ?? $part->description;

    $repo->update(
        part_id: $part->part_id,
        alias: $part->part_alias,
        name: $name,
        description: $description
    );

    // Re-fetch to confirm
    $updated = $repo->findById($part->part_id);
    $tracks = $trackRepo->findTracksByPartId($updated->part_id);

    echo json_encode(partToArray($updated, $tracks, true));
    exit;
}

// ── POST /api/v1/parts/{id or alias}/tracks — assign to track ────────────────
if ($method === 'POST' && preg_match('#^([^/]+)/tracks$#', $sub, $m)) {
    require_write();
    $part = findPart($repo, $m[1]);
    if (!$part) {
        http_response_code(404);
        echo json_encode(['error' => 'Part not found', 'query' => $m[1]]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['track_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing track_id in JSON body']);
        exit;
    }

    $track_id = (int) $input['track_id'];
    $part_role = $input['part_role'] ?? 'support';
    $is_exclusive = (bool) ($input['is_exclusive'] ?? false);

    $valid_roles = ['main', 'rail', 'support', 'connector', 'guide'];
    if (!in_array($part_role, $valid_roles)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid part_role. Valid: ' . implode(', ', $valid_roles)]);
        exit;
    }

    if ($trackRepo->trackPartExists($track_id, $part->part_id)) {
        http_response_code(409);
        echo json_encode(['error' => 'Part already assigned to this track']);
        exit;
    }

    $trackRepo->insertTrackPart($track_id, $part->part_id, $part_role);

    $tracks = $trackRepo->findTracksByPartId($part->part_id);

    echo json_encode([
        'success' => true,
        'part_id' => $part->part_id,
        'part_alias' => $part->part_alias,
        'tracks' => array_map(function ($t) {
            return [
                'track_id'     => $t->track_id,
                'track_name'   => $t->track_name,
                'part_role'    => $t->part_role ?? null,
                'is_exclusive' => (bool) ($t->part_is_exclusive ?? false),
            ];
        }, $tracks),
    ]);
    exit;
}

// ── GET /api/v1/parts — list ─────────────────────────────────────────────────
if ($method === 'GET' && $sub === '') {
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
    exit;
}

// ── GET /api/v1/parts/{id or alias} — single part ───────────────────────────
if ($method === 'GET' && $sub !== '') {
    $part = findPart($repo, $sub);
    if (!$part) {
        http_response_code(404);
        echo json_encode(['error' => 'Part not found', 'query' => $sub]);
        exit;
    }

    $tracks = $trackRepo->findTracksByPartId($part->part_id);
    echo json_encode(partToArray($part, $tracks, true));
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

// ── Helpers ──────────────────────────────────────────────────────────────────

function findPart(\Database\PartsRepository $repo, string $sub): ?\Physical\Part
{
    if (ctype_digit($sub)) {
        return $repo->findById((int) $sub);
    }
    return $repo->findByAlias($sub);
}

function partToArray(\Physical\Part $part, array $tracks, bool $detail = false): array
{
    $data = [
        'part_id'    => $part->part_id,
        'part_alias' => $part->part_alias,
        'slug'       => $part->slug,
        'name'       => $part->name,
        'has_description' => ($part->description !== '' && $part->description !== $part->name),
        'photo_count'     => count($part->photos),
        'moment_count'    => count($part->moments),
        'tracks' => array_map(function ($t) {
            return [
                'track_id'     => $t->track_id,
                'track_name'   => $t->track_name,
                'part_role'    => $t->part_role ?? null,
                'is_exclusive' => (bool) ($t->part_is_exclusive ?? false),
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
