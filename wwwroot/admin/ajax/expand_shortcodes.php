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
    $expanded_with_workers = $worker_repo->expandShortcodes($text, "worker", $langCode);
    $fully_expanded = $parts_repo->expandShortcodes($expanded_with_workers, "part", $langCode);

    // Extract the perspectives
    $worker_perspectives = $worker_repo->extractShortcodes($text, "worker", $langCode);
    $part_perspectives = $parts_repo->extractShortcodes($text, "part", $langCode);
    $all_perspectives = array_merge($worker_perspectives, $part_perspectives);

    echo json_encode([
        'expanded_text' => $fully_expanded,
        'perspectives' => $all_perspectives
    ]);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'An error occurred during shortcode expansion.', 'details' => $e->getMessage()]);
}
