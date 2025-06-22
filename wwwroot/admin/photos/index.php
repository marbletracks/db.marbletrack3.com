<?php
declare(strict_types=1);

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$repo = new \Database\PhotoRepository(db: $mla_database);
$photos = $repo->findAll();

$page = new \Template(config: $config);
$page->setTemplate(template_file: "admin/photos/index.tpl.php");
$page->set(name: "photos", value: $photos);
$page->set(name: "page_title", value: "Photo Index");
$inner = $page->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: "Photo Index");
$layout->set(name: "page_content", value: $inner);
$layout->echoToScreen();
