<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\TrackRepository;

$repo = new TrackRepository($mla_database);
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$track_id = (int) ($_GET['id'] ?? 0);
$track = $track_id > 0 ? $repo->findById($track_id) : null;

if ($submitted) {
    $alias = trim($_POST['track_alias'] ?? '');
    $name = trim($_POST['track_name'] ?? '');
    $description = trim($_POST['track_description'] ?? '');
    $marble_sizes = $_POST['marble_sizes'] ?? [];
    $is_transport = !empty($_POST['is_transport']);
    $is_splitter = !empty($_POST['is_splitter']);
    $is_landing_zone = !empty($_POST['is_landing_zone']);

    // Validation
    if ($alias === '') {
        $errors[] = "Alias is required.";
    }
    if ($name === '') {
        $errors[] = "Name is required.";
    }
    if (empty($marble_sizes)) {
        $errors[] = "At least one marble size must be selected.";
    }
    if (!$is_transport && !$is_splitter && !$is_landing_zone) {
        $errors[] = "At least one track type must be selected.";
    }

    // Check for duplicate alias
    if (!empty($alias)) {
        $existing = $repo->findByAlias($alias);
        if ($existing && (!$track || $existing->track_id !== $track->track_id)) {
            $errors[] = "A track with alias '{$alias}' already exists.";
        }
    }

    if (empty($errors)) {
        if ($track) {
            $repo->update(
                track_id: $track->track_id,
                alias: $alias,
                name: $name,
                description: $description,
                marble_sizes: $marble_sizes,
                is_transport: $is_transport,
                is_splitter: $is_splitter,
                is_landing_zone: $is_landing_zone
            );
        } else {
            $repo->insert(
                alias: $alias,
                name: $name,
                description: $description,
                marble_sizes: $marble_sizes,
                is_transport: $is_transport,
                is_splitter: $is_splitter,
                is_landing_zone: $is_landing_zone
            );
        }

        header("Location: /admin/tracks/index.php");
        exit;
    }
}

// Get connected tracks and parts if editing
$upstream = [];
$downstream = [];
$parts = [];
$available_tracks = [];
if ($track) {
    $upstream = $repo->findUpstreamTracks($track->track_id);
    $downstream = $repo->findDownstreamTracks($track->track_id);
    $parts = $repo->findPartsByTrackId($track->track_id);

    // Get all tracks except the current one for the connection dropdowns
    $all_tracks = $repo->findAll();
    $available_tracks = array_filter($all_tracks, function($t) use ($track) {
        return $t->track_id !== $track->track_id;
    });
}

$page = new \Template($config);
$page->setTemplate("admin/tracks/track.tpl.php");
$page->set("errors", $errors);
$page->set("track", $track);
$page->set("upstream", $upstream);
$page->set("downstream", $downstream);
$page->set("parts", $parts);
$page->set("available_tracks", $available_tracks);
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", $track ? "Edit " . htmlspecialchars($track->track_name) : "Create Track");
$layout->set("page_content", $inner);
$layout->echoToScreen();
