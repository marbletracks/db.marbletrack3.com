<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\PartsRepository;

$repo = new PartsRepository($mla_database, 'en');
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$part_id = (int) ($_GET['id'] ?? 0);
$part = $part_id > 0 ? $repo->findById($part_id) : null;

if ($submitted) {
    $alias = trim($_POST['part_alias'] ?? '');
    $name = trim($_POST['part_name'] ?? '');
    $description = trim($_POST['part_description'] ?? '');
    $image_urls = array_filter(array_map('trim', $_POST['image_urls'] ?? []));

    if ($alias === '') {
        $errors[] = "Alias is required.";
    }

    if (empty($errors)) {
        if ($part) {
            $repo->update(
                part_id: $part->part_id,
                alias: $alias,
                name: $name,
                description: $description
            );
            // save photos via HasPhotos
            $repo->setPartId(part_id: $part->part_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
        } else {
            $newId = $repo->insert($alias, $name, $description);
            // now save photos
            $repo->setPartId($newId);
            $repo->savePhotosFromUrls($image_urls);
        }

        header("Location: /admin/parts/index.php");
        exit;
    }
}

$page = new \Template($config);
$page->setTemplate("admin/parts/part.tpl.php");
$page->set("errors", $errors);
$page->set("part", $part);
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", $part ? "Edit Part" : "Create Part");
$layout->set("page_content", $inner);
$layout->echoToScreen();
