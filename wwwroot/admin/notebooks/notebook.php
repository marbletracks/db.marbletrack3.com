<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\NotebookRepository;

$repo = new NotebookRepository($mla_database);
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$notebook_id = (int) ($_GET['id'] ?? 0);
$notebook = $notebook_id > 0 ? $repo->findById($notebook_id) : null;

if ($submitted) {
    $title = trim($_POST['title'] ?? '');
    $created_at = trim($_POST['created_at'] ?? '');

    if ($title === '') {
        $errors[] = "Title is required.";
    }

    if (empty($errors)) {
        if ($notebook) {
            $mla_database->executeSQL(
                "UPDATE notebooks SET title = ?, created_at = ? WHERE notebook_id = ?",
                'ssi',
                [$title, $created_at, $notebook_id]
            );
        } else {
            $mla_database->executeSQL(
                "INSERT INTO notebooks (title, created_at) VALUES (?, ?)",
                'ss',
                [$title, $created_at]
            );
        }

        header("Location: /admin/notebooks/index.php");
        exit;
    }
}

$page = new \Template($config);
$page->setTemplate("admin/notebooks/notebook.tpl.php");
$page->set("errors", $errors);
$page->set("notebook", $notebook);
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", $notebook ? "Edit Notebook" : "Create Notebook");
$layout->set("page_content", $inner);
$layout->echoToScreen();
