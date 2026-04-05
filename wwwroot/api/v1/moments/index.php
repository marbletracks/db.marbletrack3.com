<?php
/**
 * GET  /api/v1/moments                   — list all moments
 * GET  /api/v1/moments?take_id=          — filter by take
 * GET  /api/v1/moments?needs_perspective=1 — only moments with identical/missing translations
 * GET  /api/v1/moments/42                — single moment by ID with translations
 * PATCH /api/v1/moments/42               — update moment notes/dates
 * POST /api/v1/moments                   — create a new moment
 */
require_once __DIR__ . '/../_auth.php';

$repo = new \Database\MomentRepository($mla_database);
$method = $_SERVER['REQUEST_METHOD'];

$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$sub = trim(preg_replace('#^/api/v1/moments#', '', $uri_path), '/');

// ── PATCH /api/v1/moments/{id}/translations — update perspective notes ───────
if ($method === 'PATCH' && preg_match('#^(\d+)/translations$#', $sub, $m)) {
    require_write();

    $moment_id = (int) $m[1];
    $moment = $repo->findById($moment_id);
    if (!$moment) {
        http_response_code(404);
        echo json_encode(['error' => 'Moment not found', 'moment_id' => $moment_id]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['translations']) || !is_array($input['translations'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Expected {"translations": [{"entity_type": "worker", "entity_id": 4, "note": "..."}]}']);
        exit;
    }

    $updated = 0;
    foreach ($input['translations'] as $t) {
        if (empty($t['entity_type']) || empty($t['entity_id']) || !isset($t['note'])) {
            continue;
        }
        $ok = $repo->updateTranslationNote(
            $moment_id,
            (int) $t['entity_id'],
            $t['entity_type'],
            $t['note']
        );
        if ($ok) $updated++;
    }

    $data = momentToArray($repo->findById($moment_id));
    $data['translations'] = $repo->findTranslations($moment_id);
    $data['translations_updated'] = $updated;

    echo json_encode($data);
    exit;
}

// ── PATCH /api/v1/moments/{id} — update moment ──────────────────────────────
if ($method === 'PATCH' && $sub !== '') {
    require_write();

    if (!ctype_digit($sub)) {
        http_response_code(400);
        echo json_encode(['error' => 'Moment ID must be numeric']);
        exit;
    }

    $moment = $repo->findById((int) $sub);
    if (!$moment) {
        http_response_code(404);
        echo json_encode(['error' => 'Moment not found', 'moment_id' => (int) $sub]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        exit;
    }

    $repo->update(
        moment_id: $moment->moment_id,
        frame_start: array_key_exists('frame_start', $input) ? $input['frame_start'] : $moment->frame_start,
        frame_end: array_key_exists('frame_end', $input) ? $input['frame_end'] : $moment->frame_end,
        take_id: array_key_exists('take_id', $input) ? $input['take_id'] : $moment->take_id,
        notes: $input['notes'] ?? $moment->notes,
        moment_date: $input['moment_date'] ?? $moment->moment_date
    );

    $updated = $repo->findById($moment->moment_id);
    $data = momentToArray($updated);
    $data['translations'] = $repo->findTranslations($updated->moment_id);

    echo json_encode($data);
    exit;
}

// ── POST /api/v1/moments — create moment ─────────────────────────────────────
if ($method === 'POST' && $sub === '') {
    require_write();

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['notes'])) {
        http_response_code(400);
        echo json_encode(['error' => 'notes is required']);
        exit;
    }

    $moment_id = $repo->insert(
        frame_start: isset($input['frame_start']) ? (int) $input['frame_start'] : null,
        frame_end: isset($input['frame_end']) ? (int) $input['frame_end'] : null,
        take_id: isset($input['take_id']) ? (int) $input['take_id'] : null,
        notes: $input['notes'],
        moment_date: $input['moment_date'] ?? null
    );

    // Link to parts and workers via translations if provided
    if (!empty($input['part_ids'])) {
        foreach ($input['part_ids'] as $part_id) {
            $repo->createTranslationIfNotExists($moment_id, (int) $part_id, 'part');
        }
    }
    if (!empty($input['worker_ids'])) {
        foreach ($input['worker_ids'] as $worker_id) {
            $repo->createTranslationIfNotExists($moment_id, (int) $worker_id, 'worker');
        }
    }
    if (!empty($input['marble_ids'])) {
        foreach ($input['marble_ids'] as $marble_id) {
            $repo->createTranslationIfNotExists($moment_id, (int) $marble_id, 'marble');
        }
    }

    $moment = $repo->findById($moment_id);
    $data = momentToArray($moment);
    $data['translations'] = $repo->findTranslations($moment_id);

    http_response_code(201);
    echo json_encode($data);
    exit;
}

// ── GET /api/v1/moments — list ───────────────────────────────────────────────
if ($method === 'GET' && $sub === '') {
    $take_id = isset($_GET['take_id']) ? (int) $_GET['take_id'] : null;
    $needs_perspective = isset($_GET['needs_perspective']) && $_GET['needs_perspective'] === '1';

    if ($needs_perspective) {
        // Find moments where at least one translation note is identical to moments.notes
        $results = $mla_database->fetchResults(
            "SELECT DISTINCT m.moment_id, m.frame_start, m.frame_end, m.take_id, m.notes, m.moment_date
             FROM moments m
             JOIN moment_translations mt ON m.moment_id = mt.moment_id
             WHERE mt.translated_note = m.notes
             ORDER BY m.moment_id ASC"
        );

        $output = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $row = $results->data;
            $moment_id = (int) $row['moment_id'];
            $data = [
                'moment_id'   => $moment_id,
                'moment_date' => $row['moment_date'],
                'frame_start' => $row['frame_start'] !== null ? (int) $row['frame_start'] : null,
                'frame_end'   => $row['frame_end'] !== null ? (int) $row['frame_end'] : null,
                'take_id'     => $row['take_id'] !== null ? (int) $row['take_id'] : null,
                'notes'       => $row['notes'],
            ];
            $data['translations'] = $repo->findTranslations($moment_id);
            $output[] = $data;
        }

        echo json_encode(['moments' => $output, 'total' => count($output)]);
        exit;
    }

    if ($take_id) {
        $moments = $repo->findWithinTakeId($take_id);
    } else {
        $moments = $repo->findAll();
    }

    $output = [];
    foreach ($moments as $moment) {
        $output[] = momentToArray($moment);
    }

    echo json_encode(['moments' => $output, 'total' => count($output)]);
    exit;
}

// ── GET /api/v1/moments/{id} — single moment ────────────────────────────────
if ($method === 'GET' && $sub !== '') {
    if (!ctype_digit($sub)) {
        http_response_code(400);
        echo json_encode(['error' => 'Moment ID must be numeric']);
        exit;
    }

    $moment = $repo->findById((int) $sub);

    if (!$moment) {
        http_response_code(404);
        echo json_encode(['error' => 'Moment not found', 'moment_id' => (int) $sub]);
        exit;
    }

    $data = momentToArray($moment);
    $data['translations'] = $repo->findTranslations($moment->moment_id);
    $data['photos'] = array_map(function ($photo) {
        return [
            'photo_id' => $photo->photo_id,
            'url'      => $photo->url,
        ];
    }, $moment->photos);

    echo json_encode($data);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

// ── Helpers ──────────────────────────────────────────────────────────────────

function momentToArray(\Media\Moment $moment): array
{
    return [
        'moment_id'   => $moment->moment_id,
        'moment_date' => $moment->moment_date,
        'frame_start' => $moment->frame_start,
        'frame_end'   => $moment->frame_end,
        'take_id'     => $moment->take_id,
        'notes'       => $moment->notes,
    ];
}
