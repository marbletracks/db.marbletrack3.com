<?php
// File: /wwwroot/admin/moments/realtime.php

declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$workers_repo = new \Database\WorkersRepository($mla_database, 'en');
$workers = $workers_repo->findAll();

$page = new \Template($config);
$page->setTemplate('admin/moments/realtime.tpl.php');
$page->set('title', 'Realtime Moments');
$page->set('workers', $workers);
$page->set('page_title', 'Realtime Moments');
$page->set('user', $is_logged_in->getLoggedInUsername());
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", "Realtime Moments");
$layout->set("page_content", $inner);
$layout->echoToScreen();

