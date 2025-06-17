<?php
declare(strict_types=1);

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$repo = new \Database\LivestreamsRepository($mla_database);
$livestreams = $repo->findAll();

$page = new \Template(config: $config);
$page->setTemplate("admin/livestreams/index.tpl.php");
$page->set("livestreams", $livestreams);
$page->set("page_title", "Livestreams");
$inner = $page->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: "Livestreams");
$layout->set(name: "page_content", value: $inner);
$layout->echoToScreen();
