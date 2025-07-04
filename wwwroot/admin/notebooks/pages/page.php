<?php

// File: /wwwroot/admin/notebooks/pages/page.php
declare(strict_types=1);

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\PageRepository;
use Database\ColumnsRepository;

$repo = new PageRepository($mla_database);
$columnsRepo = new ColumnsRepository($mla_database);
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$page_id = (int) ($_GET['id'] ?? 0);
$page = $page_id > 0 ? $repo->findById($page_id) : null;
$columns = $page_id > 0 ? $columnsRepo->findByPageId($page_id) : [];

if ($submitted) {
    $notebook_id = (int) ($_POST['notebook_id'] ?? 0);
    $number = trim($_POST['number'] ?? '');
    $created_at = trim($_POST['created_at'] ?? '');
    $image_urls = array_filter(array_map('trim', $_POST['image_urls'] ?? []));

    if ($number === '') {
        $errors[] = "Page number is required.";
    }

    if ($notebook_id === 0) {
        $errors[] = "Notebook ID is required.";
    }

    if (empty($errors)) {
        if ($page) {
            $repo->update($page_id, $notebook_id, $number, $created_at);
            $repo->setPageId($page_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
        } else {
            $new_page_id = $repo->insert($notebook_id, $number, $created_at);
            $repo->setPageId($new_page_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
        }

        header("Location: /admin/notebooks/pages/index.php");
        exit;
    }
}

$tpl = new \Template(config: $config);
$tpl->setTemplate(template_file: "admin/notebooks/pages/page.tpl.php");
$tpl->set(name: "page", value: $page);
$tpl->set(name: "columns", value: $columns);
$tpl->set(name: "errors", value: $errors);
$tpl->set(name: "page_title", value: $page ? "Edit Page {$page->number}" : "New Page");
$inner = $tpl->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: $page ? "Edit Page {$page->number}" : "New Page");
$layout->set(name: "page_content", value: $inner);
$layout->echoToScreen();
