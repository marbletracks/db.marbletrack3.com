<?php
// /admin/ajax/shortcode_filter.php?q=ds
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$q = $_GET['q'] ?? '';
$exact = isset($_GET['exact']) && $_GET['exact'] === 'true';
$res = [];

if ($q !== '') {
    $q = trim($q);
    $partsRepo = new \Database\PartsRepository($mla_database, "en");

    // First, try for an exact match on the shortcode
    // When checking for duplicates, we only want exact matches.
    $exact_matches = $partsRepo->searchByShortcodeOrName(
        like: $q,
        lang: "en",
        exact: true,
        limit: 10
    );

    // If we want partial matches, search for those too.
    // This is the case on edit pages.
    if( !$exact) {
        $like_matches = $partsRepo->searchByShortcodeOrName(
            like: $q,
            lang: "en",
            exact: false,
            limit: 10
        );
    } else {
        $like_matches = [];
    }

    // Combine and de-duplicate
    $combined = array_merge($exact_matches, $like_matches);
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
