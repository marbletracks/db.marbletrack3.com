<?php
/**
 * GET /api/v1/oss   — all outer spiral support status entries, ordered by position
 */
require_once __DIR__ . '/../_auth.php';

$repo = new \Database\PartsOSSStatusRepository($mla_database);
$statuses = $repo->findAll();

$output = [];
foreach ($statuses as $status) {
    $output[] = [
        'parts_oss_status_id' => $status->parts_oss_status_id,
        'part_id'             => $status->part_id,
        'ssop_label'          => $status->ssop_label,
        'ssop_mm'             => $status->ssop_mm,
        'height_orig'         => $status->height_orig,
        'height_best'         => $status->height_best,
        'height_now'          => $status->height_now,
        'height_delta'        => round($status->height_orig - $status->height_best, 2),
        'last_updated'        => $status->last_updated,
    ];
}

echo json_encode([
    'supports' => $output,
    'total'    => count($output),
]);
