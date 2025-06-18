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

    if ($alias === '') {
        $errors[] = "Alias is required.";
    }

    if (empty($errors)) {
        if ($part) {
            // this should be encapsulated like the insert method
            $mla_database->executeSQL(
                "UPDATE parts SET part_alias = ? WHERE part_id = ?",
                'si',
                [$alias,
                $part->part_id]
            );

            $mla_database->executeSQL(
                "REPLACE INTO part_translations (
                    part_id,
                    language_code,
                    part_name,
                    part_description
                ) VALUES (?, 'en', ?, ?)",
                'iss',
                [
                    $part->part_id,
                    $name,
                    $description,
                ]
            );

        } else {
            $repo->insert($alias, $name, $description);
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
