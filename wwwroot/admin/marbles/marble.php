<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$repo = new \Database\MarblesRepository($mla_database);
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$marble_id = (int) ($_GET['id'] ?? 0);
$marble = $marble_id > 0 ? $repo->findById($marble_id) : null;

if ($submitted) {
    $marble_alias = trim($_POST['marble_alias'] ?? '');
    $marble_name = trim($_POST['marble_name'] ?? '');
    $team_name = trim($_POST['team_name'] ?? '') ?: null;
    $size = $_POST['size'] ?? 'small';
    $color = trim($_POST['color'] ?? '');
    $quantity = (int) ($_POST['quantity'] ?? 1);
    $description = trim($_POST['description'] ?? '') ?: null;

    if ($marble_alias === '') {
        $errors[] = "Alias is required.";
    }
    if ($marble_name === '') {
        $errors[] = "Name is required.";
    }
    if ($color === '') {
        $errors[] = "Color is required.";
    }

    if (empty($errors)) {
        if ($marble) {
            $repo->update(
                marble_id: $marble_id,
                alias: $marble_alias,
                name: $marble_name,
                size: $size,
                color: $color,
                quantity: $quantity,
                team_name: $team_name,
                description: $description
            );
        } else {
            $marble_id = $repo->insert(
                alias: $marble_alias,
                name: $marble_name,
                size: $size,
                color: $color,
                quantity: $quantity,
                team_name: $team_name,
                description: $description
            );
        }

        header("Location: /admin/marbles/index.php");
        exit;
    }
}

$page = new \Template($config);
$page->setTemplate("admin/marbles/marble.tpl.php");
$page->set("errors", $errors);
$page->set("marble", $marble);
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", $marble ? "Edit Marble" : "Add Marble");
$layout->set("page_content", $inner);
$layout->echoToScreen();
