<?php
declare(strict_types=1);

// File: /wwwroot/admin/ajax/expand_shortcodes.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

header('Content-Type: application/json');

$text = $_POST['text'] ?? '';
$langCode = 'en'; // Assuming 'en' for now

if (empty($text)) {
    echo json_encode(['expanded_text' => '']);
    exit;
}

try {
    $worker_repo = new \Database\WorkersRepository($mla_database, $langCode);
    $parts_repo = new \Database\PartsRepository($mla_database, $langCode);

    // First expand workers, then parts on the result of the first expansion
    $expanded_with_workers = $worker_repo->expandShortcodesForBackend($text, "worker", $langCode);
    $fully_expanded = $parts_repo->expandShortcodesForBackend($expanded_with_workers, "part", $langCode);

    // Extract the perspectives
    $worker_perspectives = $worker_repo->extractShortcodes($text, "worker", $langCode);
    $part_perspectives = $parts_repo->extractShortcodes($text, "part", $langCode);

    // Add unused photos to worker perspectives
    foreach ($worker_perspectives as &$perspective) {
        if ($perspective['type'] === 'worker') {
            $unused_photos = $worker_repo->getUnusedPhotos((int)$perspective['id']);
            error_log("DEBUG: Worker {$perspective['name']} (ID: {$perspective['id']}) has " . count($unused_photos) . " unused photos");

            // Convert Photo objects to arrays with both thumbnail and full URLs
            $photo_data = [];
            foreach ($unused_photos as $photo) {
                $photo_info = [
                    'photo_id' => $photo->photo_id,
                    'thumbnail_url' => $photo->getThumbnailUrl(),
                    'full_url' => $photo->getUrl()
                ];
                $photo_data[] = $photo_info;
                error_log("  - Photo ID: {$photo->photo_id}, Thumbnail: {$photo_info['thumbnail_url']}, Full: {$photo_info['full_url']}");
            }

            $perspective['unused_photos'] = $photo_data;
        }
    }

    $all_perspectives = array_merge($worker_perspectives, $part_perspectives);

    echo json_encode([
        'expanded_text' => $fully_expanded,
        'perspectives' => $all_perspectives
    ]);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'An error occurred during shortcode expansion.', 'details' => $e->getMessage()]);
}
