<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$livestreamId = (int) ($_GET['livestream_id'] ?? 0);
$lsRepo = new \Database\LivestreamsRepository($mla_database);
$stream = $lsRepo->findById($livestreamId);

if (!$stream) {
    die("Livestream ID {$livestreamId} not found.");
}

$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($submitted) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') {
        $errors[] = 'Title is required.';
    }

    if (empty($errors)) {
        $epRepo = new \Database\EpisodeRepository($mla_database);
        $newId = $epRepo->insert($title, $description, $livestreamId);
        header("Location: /admin/episodes/");
        exit;
    }
}

$defaultTitle = $stream->title;
$defaultDesc = $stream->description; // cropped; see note below

$page = new \Template(config: $config);
$page->setTemplate("admin/episodes/create.tpl.php");
$page->set("errors", $errors);
$page->set("defaultTitle", $defaultTitle);
$page->set("defaultDesc", $defaultDesc);
$page->set("livestream", $stream);

$inner = $page->grabTheGoods();
$layout = new \Template(config: $config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", "Create Episode");
$layout->set("page_content", $inner);
$layout->echoToScreen();
