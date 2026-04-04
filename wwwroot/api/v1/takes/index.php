<?php
/**
 * GET /api/v1/takes            — list all takes
 * GET /api/v1/takes?snippets=1 — only snippets
 */
require_once __DIR__ . '/../_auth.php';

$repo = new \Database\TakeRepository($mla_database);

$snippets_only = isset($_GET['snippets']) && $_GET['snippets'] === '1';

if ($snippets_only) {
    $takes = $repo->findSnippets();
} else {
    $takes = $repo->findAll();
}

$output = [];
foreach ($takes as $take) {
    $output[] = [
        'take_id'   => $take->take_id,
        'take_name' => $take->take_name,
    ];
}

echo json_encode(['takes' => $output, 'total' => count($output)]);
