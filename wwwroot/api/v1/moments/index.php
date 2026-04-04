<?php
/**
 * GET  /api/v1/moments          — list all moments
 * GET  /api/v1/moments?take_id= — filter by take
 * GET  /api/v1/moments/42       — single moment by ID with translations
 * POST /api/v1/moments          — create a new moment
 */
require_once __DIR__ . '/../_auth.php';

$repo = new \Database\MomentRepository($mla_database);
$method = $_SERVER['REQUEST_METHOD'];

$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$sub = trim(preg_replace('#^/api/v1/moments#', '', $uri_path), '/');

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
