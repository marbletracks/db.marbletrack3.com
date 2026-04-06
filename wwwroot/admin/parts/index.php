<?php
declare(strict_types=1);

# Must include here because DH runs FastCGI https://www.phind.com/search?cache=zfj8o8igbqvaj8cm91wp1b7k
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

// Redirect if not logged in
if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

// Repository knows how to connect to the database
$repo = new \Database\PartsRepository(
    db: $mla_database,
    langCode: 'en',
);
$trackRepo = new \Database\TrackRepository($mla_database);

// Get filter parameters
$filter = trim($_GET['filter'] ?? '');
$status = $_GET['status'] ?? 'all';

// Fetch parts (filtered if filter provided)
if (!empty($filter)) {
    $parts = $repo->findByFilter($filter);
} else {
    $parts = $repo->findAll();
}

// Get track associations for all parts
$partTracks = [];
foreach ($parts as $part) {
    $partTracks[$part->part_id] = $trackRepo->findTracksByPartId($part->part_id);
}

// Apply status filter
if ($status === 'needs_work') {
    $parts = array_filter($parts, function($part) {
        return trim($part->description) === '' || trim($part->description) === trim($part->name);
    });
} elseif ($status === 'unassigned') {
    $parts = array_filter($parts, function($part) use ($partTracks) {
        return empty($partTracks[$part->part_id]);
    });
} elseif ($status === 'complete') {
    $parts = array_filter($parts, function($part) use ($partTracks) {
        $hasGoodDescription = trim($part->description) !== '' && trim($part->description) !== trim($part->name);
        $hasTrackAssignment = !empty($partTracks[$part->part_id]);
        return $hasGoodDescription && $hasTrackAssignment;
    });
}

$page = new \Template(config: $config);
$page->setTemplate(template_file: "admin/parts/index.tpl.php");
$page->set(name: "parts", value: $parts);
$page->set(name: "filter", value: $filter);
$page->set(name: "status", value: $status);
$page->set(name: "partTracks", value: $partTracks);
$inner = $page->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: "Parts");
$layout->set(name: "page_content", value: $inner);
$layout->echoToScreen();
