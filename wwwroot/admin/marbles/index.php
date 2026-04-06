<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$repo = new \Database\MarblesRepository($mla_database);
$marbles = $repo->findAll();

$page = new \Template($config);
$page->setTemplate("admin/marbles/index.tpl.php");
$page->set("marbles", $marbles);
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", "Marbles");
$layout->set("page_content", $inner);
$layout->echoToScreen();
