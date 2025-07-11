<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\PartsRepository;
use Database\MomentRepository;

$repo = new PartsRepository($mla_database, 'en');
$moment_repo = new MomentRepository($mla_database);
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$part_id = (int) ($_GET['id'] ?? 0);
$part = $part_id > 0 ? $repo->findById($part_id) : null;

if ($part) {
    [$prioritized_moments, $other_moments] = $moment_repo->findAllForEntity($part->name, $part->part_alias);
} else {
    $prioritized_moments = [];
    $other_moments = $moment_repo->findAll();
}


if ($submitted) {
    $alias = trim($_POST['part_alias'] ?? '');
    $name = trim($_POST['part_name'] ?? '');
    $description = trim($_POST['part_description'] ?? '');
    $image_urls = array_filter(array_map('trim', $_POST['image_urls'] ?? []));
    $moment_ids_str = $_POST['moment_ids'] ?? '';
    $moment_ids = $moment_ids_str ? explode(',', $moment_ids_str) : [];

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
            $repo->saveMoments(moment_ids: $moment_ids);
        } else {
            $newId = $repo->insert($alias, $name, $description);
            // now save photos
            $repo->setPartId($newId);
            $repo->savePhotosFromUrls($image_urls);
            $repo->saveMoments(moment_ids: $moment_ids);
        }

        header("Location: /admin/parts/index.php");
        exit;
    }
}

$page = new \Template($config);
$page->setTemplate("admin/parts/part.tpl.php");
$page->set("errors", $errors);
$page->set("part", $part);
$page->set("prioritized_moments", $prioritized_moments);
$page->set("other_moments", $other_moments);
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", $part ? "Edit Part" : "Create Part");
$layout->set("page_content", $inner);
$layout->echoToScreen();
