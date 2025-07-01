<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\WorkersRepository;

$repo = new WorkersRepository($mla_database, "en");
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$worker_id = (int) ($_GET['id'] ?? 0);
$worker = $worker_id > 0 ? $repo->findById($worker_id) : null;

if ($submitted) {
    $worker_alias = trim($_POST['worker_alias'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_urls = array_filter(array_map('trim', $_POST['image_urls'] ?? []));

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
            // should probably be in WorkersRepository
            $repo->setWorkerId($worker_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
        } else {
            $worker_id = $repo->insert(
                alias: $worker_alias,
                name: $name,
                description: $description
            );
            $repo->setWorkerId($worker_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
        }

        header("Location: /admin/workers/index.php");
        exit;
    }
}

$page = new \Template($config);
$page->setTemplate("admin/workers/worker.tpl.php");
$page->set("errors", $errors);
$page->set("worker", $worker);
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", $worker ? "Edit Worker" : "Create Worker");
$layout->set("page_content", $inner);
$layout->echoToScreen();
