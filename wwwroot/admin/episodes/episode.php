<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\EpisodeRepository;

$epRepo = new EpisodeRepository($mla_database);
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$episode_id = (int) ($_GET['episode_id'] ?? 0);
$episode = $episode_id > 0 ? $epRepo->findById($episode_id) : null;

// For creation mode, check if we have a livestream_id
$livestreamId = (int) ($_GET['livestream_id'] ?? 0);
$defaultTitle = '';
$defaultDesc = '';
$streamCode = '';

// If creating from a livestream, get the livestream details
if (!$episode && $livestreamId > 0) {
    $lsRepo = new \Database\LivestreamsRepository($mla_database);
    $stream = $lsRepo->findById($livestreamId);
    if ($stream) {
        $defaultTitle = $stream->title;
        $defaultDesc = $stream->description;
        $streamCode = $stream->external_id;
    }
}

if ($submitted) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $postLivestreamId = (int) ($_POST['livestream_id'] ?? 0);

    if ($title === '') {
        $errors[] = 'Title is required.';
    }

    if (empty($errors)) {
        if ($episode) {
            // Update existing episode
            $success = $epRepo->update($episode_id, $title, $description, $postLivestreamId ?: null);
            if (!$success) {
                $errors[] = 'Failed to update episode. Please try again.';
            } else {
                header("Location: /admin/episodes/");
                exit;
            }
        } else {
            // Create new episode
            $newId = $epRepo->insert($title, $description, $postLivestreamId ?: null);
            if ($newId === false) {
                $errors[] = 'Failed to create episode. Please try again.';
            } else {
                // If created from a livestream, update its status
                if ($postLivestreamId > 0) {
                    $lr = new \Database\LivestreamsRepository($mla_database);
                    $lr->setLivestreamStatus(
                        livestream_id: $postLivestreamId,
                        status: 'has'
                    );
                }
                header("Location: /admin/episodes/");
                exit;
            }
        }
    }
}

$page = new \Template(config: $config);
$page->setTemplate("admin/episodes/episode.tpl.php");
$page->set("errors", $errors);
$page->set("episode", $episode);
$page->set("defaultTitle", $episode ? $episode->title : $defaultTitle);
$page->set("defaultDesc", $episode ? $episode->episode_english_description : $defaultDesc);
$page->set("defaultLivestreamId", $episode ? $episode->livestream_id : $livestreamId);
$page->set("streamCode", $streamCode);

$inner = $page->grabTheGoods();
$layout = new \Template(config: $config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", $episode ? "Edit Episode" : "Create Episode");
$layout->set("page_content", $inner);
$layout->echoToScreen();
