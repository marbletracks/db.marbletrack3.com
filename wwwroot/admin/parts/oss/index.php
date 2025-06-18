<?php
// File: /admin/parts/oss/index.php

declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\PartsOSSStatusRepository;

$repo = new PartsOSSStatusRepository($mla_database);
$ossParts = $repo->findAll();

$page = new Template(config: $config);
$page->setTemplate("admin/parts/oss/index.tpl.php");
$page->set("ossParts", $ossParts);
$inner = $page->grabTheGoods();

$layout = new Template(config: $config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", "Outer Spiral Supports");
$layout->set("page_content", $inner);
$layout->echoToScreen();