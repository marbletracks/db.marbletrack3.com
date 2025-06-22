<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\PhotoRepository;

$repo = new PhotoRepository($mla_database);
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$photo_id = (int) ($_GET['id'] ?? 0);
$photo = $photo_id > 0 ? $repo->findById($photo_id) : null;

if ($submitted) {
    $code = trim($_POST['code'] ?? '');
    $url = trim($_POST['url'] ?? '');

    if ($code === '' && $url === '') {
        $errors[] = "At least one of code or URL is required.";
    }

    if (empty($errors)) {
        if ($photo) {
            $mla_database->executeSQL(
                "UPDATE photos SET code = ?, url = ? WHERE photo_id = ?",
                'ssi',
                [$code, $url, $photo_id]
            );
        } else {
            $mla_database->executeSQL(
                "INSERT INTO photos (code, url) VALUES (?, ?)",
                'ss',
                [$code, $url]
            );
            // $photo_id = $mla_database->getInsertId();
        }

        header("Location: /admin/photos/index.php");
        exit;
    }
}

$page = new \Template($config);
$page->setTemplate("admin/photos/photo.tpl.php");
$page->set("errors", $errors);
$page->set("photo", $photo);
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", $photo ? "Edit Photo" : "Create Photo");
$layout->set("page_content", $inner);
$layout->echoToScreen();
