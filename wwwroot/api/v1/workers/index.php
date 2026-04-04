<?php
/**
 * GET /api/v1/workers          — list all workers
 * GET /api/v1/workers/3        — single worker by ID
 * GET /api/v1/workers/gc       — single worker by alias
 */
require_once __DIR__ . '/../_auth.php';

$repo = new \Database\WorkersRepository($mla_database, 'en');

$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$sub = trim(preg_replace('#^/api/v1/workers#', '', $uri_path), '/');

if ($sub === '') {
    $workers = $repo->findAll();

    $output = [];
    foreach ($workers as $worker) {
        $output[] = workerToArray($worker);
    }

    echo json_encode(['workers' => $output, 'total' => count($output)]);
} else {
    if (ctype_digit($sub)) {
        $worker = $repo->findById((int) $sub);
    } else {
        $worker = $repo->findByAlias($sub);
    }

    if (!$worker) {
        http_response_code(404);
        echo json_encode(['error' => 'Worker not found', 'query' => $sub]);
        exit;
    }

    echo json_encode(workerToArray($worker, true));
}

function workerToArray(\Physical\Worker $worker, bool $detail = false): array
{
    $data = [
        'worker_id'    => $worker->worker_id,
        'worker_alias' => $worker->worker_alias,
        'slug'         => $worker->slug,
        'name'         => $worker->name,
        'photo_count'  => count($worker->photos),
        'moment_count' => count($worker->moments),
    ];

    if ($detail) {
        $data['description'] = $worker->description;
        $data['photos'] = array_map(function ($photo) {
            return [
                'photo_id' => $photo->photo_id,
                'url'      => $photo->url,
            ];
        }, $worker->photos);
        $data['moments'] = array_map(function ($moment) {
            return [
                'moment_id'   => $moment->moment_id,
                'moment_date' => $moment->moment_date ?? null,
                'frame_start' => $moment->frame_start ?? null,
                'frame_end'   => $moment->frame_end ?? null,
                'notes'       => $moment->notes ?? null,
            ];
        }, $worker->moments);
    }

    return $data;
}
