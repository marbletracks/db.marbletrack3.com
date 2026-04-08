<?php
/**
 * GET   /api/v1/marbles          — list all marbles
 * GET   /api/v1/marbles/42       — single marble by ID
 * GET   /api/v1/marbles/Lb       — single marble by alias
 * PATCH /api/v1/marbles/42       — update marble
 * POST  /api/v1/marbles          — create marble
 */
require_once __DIR__ . '/../_auth.php';

$repo = new \Database\MarblesRepository($mla_database);
$method = $_SERVER['REQUEST_METHOD'];

$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$sub = trim(preg_replace('#^/api/v1/marbles#', '', $uri_path), '/');

// ── POST /api/v1/marbles — create ───────────────────────────────────────────
if ($method === 'POST' && $sub === '') {
    require_write();

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['marble_alias']) || empty($input['marble_name']) || empty($input['size']) || empty($input['color'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Required: marble_alias, marble_name, size, color']);
        exit;
    }

    $valid_sizes = ['small', 'medium', 'large'];
    if (!in_array($input['size'], $valid_sizes)) {
        http_response_code(400);
        echo json_encode(['error' => 'size must be: small, medium, or large']);
        exit;
    }

    $marble_id = $repo->insert(
        alias: $input['marble_alias'],
        name: $input['marble_name'],
        size: $input['size'],
        color: $input['color'],
        quantity: (int) ($input['quantity'] ?? 1),
        team_name: $input['team_name'] ?? null,
        description: $input['description'] ?? null
    );

    $marble = $repo->findById($marble_id);

    http_response_code(201);
    echo json_encode(marbleToArray($marble));
    exit;
}

// ── PATCH /api/v1/marbles/{id or alias} — update ────────────────────────────
if ($method === 'PATCH' && $sub !== '') {
    require_write();

    $marble = findMarble($repo, $sub);
    if (!$marble) {
        http_response_code(404);
        echo json_encode(['error' => 'Marble not found', 'query' => $sub]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        exit;
    }

    $repo->update(
        marble_id: $marble->marble_id,
        alias: $input['marble_alias'] ?? $marble->marble_alias,
        name: $input['marble_name'] ?? $marble->marble_name,
        size: $input['size'] ?? $marble->size,
        color: $input['color'] ?? $marble->color,
        quantity: (int) ($input['quantity'] ?? $marble->quantity),
        team_name: array_key_exists('team_name', $input) ? $input['team_name'] : $marble->team_name,
        description: array_key_exists('description', $input) ? $input['description'] : $marble->description
    );

    // Handle photo_urls: add photos without removing existing ones
    if (array_key_exists('photo_urls', $input) && is_array($input['photo_urls'])) {
        $repo->setMarbleId($marble->marble_id);
        $urls = array_filter(array_map('trim', $input['photo_urls']));
        if (!empty($urls)) {
            $repo->addPhotosFromUrls($urls);
        }
    }

    $updated = $repo->findById($marble->marble_id);
    echo json_encode(marbleToArray($updated));
    exit;
}

// ── GET /api/v1/marbles — list ──────────────────────────────────────────────
if ($method === 'GET' && $sub === '') {
    $marbles = $repo->findAll();

    $output = [];
    foreach ($marbles as $marble) {
        $output[] = marbleToArray($marble);
    }

    echo json_encode(['marbles' => $output, 'total' => count($output)]);
    exit;
}

// ── GET /api/v1/marbles/{id or alias} — single ─────────────────────────────
if ($method === 'GET' && $sub !== '') {
    $marble = findMarble($repo, $sub);
    if (!$marble) {
        http_response_code(404);
        echo json_encode(['error' => 'Marble not found', 'query' => $sub]);
        exit;
    }

    echo json_encode(marbleToArray($marble));
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

// ── Helpers ─────────────────────────────────────────────────────────────────

function findMarble(\Database\MarblesRepository $repo, string $sub): ?\Physical\Marble
{
    if (ctype_digit($sub)) {
        return $repo->findById((int) $sub);
    }
    return $repo->findByAlias($sub);
}

function marbleToArray(\Physical\Marble $marble): array
{
    $photos = array_map(fn($p) => [
        'photo_id' => $p->photo_id,
        'url'      => $p->getUrl(),
    ], $marble->photos);

    return [
        'marble_id'    => $marble->marble_id,
        'marble_alias' => $marble->marble_alias,
        'slug'         => $marble->slug,
        'marble_name'  => $marble->marble_name,
        'team_name'    => $marble->team_name,
        'size'         => $marble->size,
        'color'        => $marble->color,
        'quantity'     => $marble->quantity,
        'description'  => $marble->description,
        'photos'       => $photos,
    ];
}
