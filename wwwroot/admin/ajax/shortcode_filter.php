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
    $res = $partsRepo->searchByShortcodeOrName(
        like: $q,
        lang: "en",
        exact: $exact,
        limit: 10
    );
}

header('Content-Type: application/json');
echo json_encode($res);
