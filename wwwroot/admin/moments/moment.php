<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\MomentRepository;
use Database\TakeRepository;

// grab these in case we are tryna populate a new Moment from Moment Index
$default_frame_start = $mla_request->get['frame_start'] ?? "";
$default_take_id = $mla_request->get['take_id'] ?? "";

$moment_repo = new MomentRepository($mla_database);
$take_repo = new TakeRepository($mla_database);
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$moment_id = (int) ($_GET['id'] ?? 0);
$moment = $moment_id > 0 ? $moment_repo->findById($moment_id) : null;
$translations = $moment ? $moment_repo->findTranslations($moment_id) : [];
$takes = $take_repo->findSnippets();

if ($submitted) {
    $notes = trim($_POST['notes'] ?? '');
    $frame_start = filter_input(INPUT_POST, 'frame_start', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $frame_end = filter_input(INPUT_POST, 'frame_end', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $take_id = filter_input(INPUT_POST, 'take_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $moment_date = trim($_POST['moment_date'] ?? '');
    $image_urls = array_filter(array_map('trim', $_POST['image_urls'] ?? []));
    $perspectives = $_POST['perspectives'] ?? [];

    // filter_input returns false on failure, and null if the variable is not set. We want to store null in the DB
    $frame_start = ($frame_start === false) ? null : $frame_start;
    $frame_end = ($frame_end === false) ? null : $frame_end;
    $take_id = ($take_id === false) ? null : $take_id;
    if (empty($moment_date)) {
        $moment_date = null;
    } elseif (\DateTime::createFromFormat('Y-m-d', $moment_date) === false) {
        $errors[] = 'Invalid date format for Moment Date. Please use YYYY-MM-DD.';
    }

    if (empty($errors)) {
        if ($moment) {
            $mla_database->executeSQL(
                "UPDATE moments SET notes = ?, frame_start = ?, frame_end = ?, take_id = ?, moment_date = ? WHERE moment_id = ?",
                'siiisi',
                [$notes, $frame_start, $frame_end, $take_id, $moment_date, $moment_id]
            );
            $moment_repo->setMomentId(moment_id: $moment_id);
            $moment_repo->savePhotosFromUrls(urls: $image_urls);
            $moment_repo->saveTranslations(moment_id: $moment_id, perspectives: $perspectives);
        } else {
            $new_moment_id = $moment_repo->insert($frame_start, $frame_end, $take_id, $notes, $moment_date);
            $moment_repo->setMomentId(moment_id: $new_moment_id);
            $moment_repo->savePhotosFromUrls(urls: $image_urls);
            $moment_repo->saveTranslations(moment_id: $new_moment_id, perspectives: $perspectives);
        }

        header("Location: /admin/moments/index.php");
        exit;
    }
}

$page = new \Template($config);
$page->setTemplate("admin/moments/moment.tpl.php");
$page->set("errors", $errors);
$page->set("moment", $moment);
$page->set(name: "default_frame_start", value: $default_frame_start);
$page->set(name: "default_take_id", value: $default_take_id);
$page->set("translations", $translations);
$page->set("takes", $takes);
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", $moment ? "Edit Moment" : "Create Moment");
$layout->set("page_content", $inner);
$layout->echoToScreen();
