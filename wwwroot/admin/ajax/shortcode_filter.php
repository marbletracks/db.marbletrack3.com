<?php
// /admin/ajax/shortcode_filter.php?q=ds
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$q = $_GET['q'] ?? '';
$exact = isset($_GET['exact']) && $_GET['exact'] === 'true';
header('Content-Type: application/json');

$langCode = "en";
$res = [];

if ($q !== '') {
    $q = trim($q);
    $partsRepo = new \Database\PartsRepository($mla_database, $langCode);
    $workersRepo = new \Database\WorkersRepository($mla_database, $langCode);

    // First, try for an exact match on the shortcode
    // When checking for duplicates, we only want exact matches.
    $exact_worker_matches = $workersRepo->searchByShortcodeOrName(
        like: $q,
        lang: $langCode,
        exact: true,
        limit: 10
    );
    $exact_part_matches = $partsRepo->searchByShortcodeOrName(
        like: $q,
        lang: $langCode,
        exact: true,
        limit: 10
    );

    // If we want partial matches, search for those too.
    // This is the case on edit pages.
    if( !$exact) {
        $like_part_matches = $partsRepo->searchByShortcodeOrName(
            like: $q,
            lang: $langCode,
            exact: false,
            limit: 10
        );
        $like_worker_matches = $workersRepo->searchByShortcodeOrName(
            like: $q,
            lang: $langCode,
            exact: false,
            limit: 10
        );
    } else {
        $like_part_matches = [];
        $like_worker_matches = [];
    }

    // Combine and de-duplicate
    $combined = array_merge($exact_worker_matches, $exact_part_matches, $like_part_matches, $like_part_matches);
    $unique_results = [];
    $seen_aliases = [];

    foreach ($combined as $item) {
        if (!isset($item['alias'])) {
            continue;
        }
        if (!in_array($item['alias'], $seen_aliases)) {
            $unique_results[] = $item;
            $seen_aliases[] = $item['alias'];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($unique_results);
