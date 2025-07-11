<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\WorkersRepository;
use Database\MomentRepository;

$repo = new WorkersRepository($mla_database, "en");
$moment_repo = new MomentRepository($mla_database);
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$worker_id = (int) ($_GET['id'] ?? 0);
$worker = $worker_id > 0 ? $repo->findById($worker_id) : null;
$all_moments = $moment_repo->findAll();

if ($submitted) {
    $worker_alias = trim($_POST['worker_alias'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_urls = array_filter(array_map('trim', $_POST['image_urls'] ?? []));
    $moment_ids_str = $_POST['moment_ids'] ?? '';
    $moment_ids = $moment_ids_str ? explode(',', $moment_ids_str) : [];

    if ($worker_alias === '') {
        $errors[] = "Alias is required.";
    }
    if ($name === '') {
        $errors[] = "Name is required.";
    }

    if (empty($errors)) {
        if ($worker) {
            $repo->update(
                worker_id: $worker_id,
                alias: $worker_alias,
                name: $name,
                description: $description
            );
            $repo->setWorkerId($worker_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
            $repo->saveMoments(moment_ids: $moment_ids);
        } else {
            $worker_id = $repo->insert(
                alias: $worker_alias,
                name: $name,
                description: $description
            );
            $repo->setWorkerId($worker_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
            $repo->saveMoments(moment_ids: $moment_ids);
        }

        header("Location: /admin/workers/index.php");
        exit;
    }
}

$page = new \Template($config);
$page->setTemplate("admin/workers/worker.tpl.php");
$page->set("errors", $errors);
$page->set("worker", $worker);
$page->set("all_moments", $all_moments);
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", $worker ? "Edit Worker" : "Create Worker");
$layout->set("page_content", $inner);
$layout->echoToScreen();
