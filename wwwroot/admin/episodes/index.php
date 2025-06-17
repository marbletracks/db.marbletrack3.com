<?php
declare(strict_types=1);

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$repo = new \Database\EpisodeRepository($mla_database);
$episodes = $repo->findAll();

$page = new \Template(config: $config);
$page->setTemplate("admin/episodes/index.tpl.php");
$page->set("episodes", $episodes);
$page->set("page_title", "Episodes");
$inner = $page->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: "Episodes");
$layout->set(name: "page_content", value: $inner);
$layout->echoToScreen();
