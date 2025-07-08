<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\MomentRepository;
use Database\TakeRepository;

$moment_repo = new MomentRepository($mla_database);
$take_repo = new TakeRepository($mla_database);
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$moment_id = (int) ($_GET['id'] ?? 0);
$moment = $moment_id > 0 ? $moment_repo->findById($moment_id) : null;
$takes = $take_repo->findAll();

if ($submitted) {
    $notes = trim($_POST['notes'] ?? '');
    $frame_start = filter_var($_POST['frame_start'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $frame_end = filter_var($_POST['frame_end'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $phrase_id = filter_var($_POST['phrase_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $take_id = filter_var($_POST['take_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $image_urls = array_filter(array_map('trim', $_POST['image_urls'] ?? []));

    if (empty($errors)) {
        if ($moment) {
            $mla_database->executeSQL(
                "UPDATE moments SET notes = ?, frame_start = ?, frame_end = ?, phrase_id = ?, take_id = ? WHERE moment_id = ?",
                'siiiii',
                [$notes, $frame_start, $frame_end, $phrase_id, $take_id, $moment_id]
            );
            $moment_repo->setMomentId(moment_id: $moment_id);
            $moment_repo->savePhotosFromUrls(urls: $image_urls);
        } else {
            $mla_database->executeSQL(
                "INSERT INTO moments (notes, frame_start, frame_end, phrase_id, take_id) VALUES (?, ?, ?, ?, ?)",
                'siiii',
                [$notes, $frame_start, $frame_end, $phrase_id, $take_id]
            );
            $moment_repo->setMomentId(moment_id: $mla_database->insertId());
            $moment_repo->savePhotosFromUrls(urls: $image_urls);
        }

        header("Location: /admin/moments/index.php");
        exit;
    }
}

$page = new \Template($config);
$page->setTemplate("admin/moments/moment.tpl.php");
$page->set("errors", $errors);
$page->set("moment", $moment);
$page->set("takes", $takes);
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", $moment ? "Edit Moment" : "Create Moment");
$layout->set("page_content", $inner);
$layout->echoToScreen();
