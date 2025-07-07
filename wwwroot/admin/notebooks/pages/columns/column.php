<?php

// File: /wwwroot/admin/notebooks/pages/columns/column.php
declare(strict_types=1);

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\ColumnsRepository;
use Database\WorkersRepository;
use Database\PageRepository;
use Database\TokensRepository;

$columnsRepo = new ColumnsRepository($mla_database);
$workersRepo = new WorkersRepository($mla_database, "en");
$pageRepo = new PageRepository($mla_database);
$tokensRepo = new TokensRepository($mla_database);
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$column_id = (int) ($_GET['id'] ?? 0);
$page_id = (int) ($_GET['page_id'] ?? 0);
$column = $column_id > 0 ? $columnsRepo->findById($column_id) : null;
$page = null;

// If we're creating a new column, we need a page_id
if (!$column && $page_id > 0) {
    $page = $pageRepo->findById($page_id);
    if (!$page) {
        $errors[] = "Page not found.";
    }
} elseif ($column) {
    $page = $pageRepo->findById($column->page_id);
    $page_id = $column->page_id;
}

if (!$page_id) {
    $errors[] = "Page ID is required.";
}

// Get all workers for the dropdown
$workers = $workersRepo->findAll();

// Get tokens for this column if it exists
$tokens = [];
if ($column) {
    $tokens = $tokensRepo->findByColumnId($column->column_id);
}

if ($submitted) {
    $worker_id = (int) ($_POST['worker_id'] ?? 0);
    $col_name = trim($_POST['col_name'] ?? '');
    $col_sort = (int) ($_POST['col_sort'] ?? 0);

    if ($worker_id === 0) {
        $errors[] = "Worker is required.";
    }
    if ($col_name === '') {
        $errors[] = "Column name is required.";
    }

    if (empty($errors)) {
        if ($column) {
            $columnsRepo->update($column_id, $page_id, $worker_id, $col_name, $col_sort);
        } else {
            $columnsRepo->insert($page_id, $worker_id, $col_name, $col_sort);
        }

        header("Location: /admin/notebooks/pages/page.php?id={$page_id}");
        exit;
    }
}

$tpl = new \Template(config: $config);
$tpl->setTemplate(template_file: "admin/notebooks/pages/columns/column.tpl.php");
$tpl->set(name: "column", value: $column);
$tpl->set(name: "page", value: $page);
$tpl->set(name: "workers", value: $workers);
$tpl->set(name: "tokens", value: $tokens);
$tpl->set(name: "errors", value: $errors);
$tpl->set(name: "page_title", value: $column ? "Edit Column" : "Create Column");
$inner = $tpl->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: $column ? "Edit Column" : "Create Column");
$layout->set(name: "page_content", value: $inner);
$layout->echoToScreen();
