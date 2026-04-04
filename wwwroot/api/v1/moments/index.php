<?php
/**
 * GET /api/v1/moments          — list all moments
 * GET /api/v1/moments?take_id= — filter by take
 * GET /api/v1/moments/42       — single moment by ID with translations
 */
require_once __DIR__ . '/../_auth.php';

$repo = new \Database\MomentRepository($mla_database);

$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$sub = trim(preg_replace('#^/api/v1/moments#', '', $uri_path), '/');

if ($sub === '') {
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
} else {
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
}

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
