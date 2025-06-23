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
            $mla_database->executeSQL(
                "UPDATE workers SET worker_alias = ? WHERE worker_id = ?",
                'si',
                [$worker_alias, $worker_id]
            );
            $mla_database->executeSQL(
                "UPDATE worker_names SET worker_name = ?, worker_description = ? WHERE worker_id = ? AND language_code = 'en'",
                'ssi',
                [$name, $description, $worker_id]
            );
            $repo->setWorkerId($worker_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
        } else {
            $mla_database->executeSQL(
                "INSERT INTO workers (worker_alias) VALUES (?)",
                's',
                [$worker_alias]
            );
            $worker_id = $mla_database->insertId();
            $mla_database->executeSQL(
                "INSERT INTO worker_names (worker_id, language_code, worker_name, worker_description) VALUES (?, 'en', ?, ?)",
                'iss',
                [$worker_id, $name, $description]
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
