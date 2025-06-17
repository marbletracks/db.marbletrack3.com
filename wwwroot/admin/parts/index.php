<?php
declare(strict_types=1);

# Must include here because DH runs FastCGI https://www.phind.com/search?cache=zfj8o8igbqvaj8cm91wp1b7k
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

// Redirect if not logged in
if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

// Repository knows how to connect to the database
$repo = new \Database\PartsRepository(
    db: $mla_database,
    langCode: 'en',
);

// Fetch parts
$parts = $repo->findAll();

$page = new \Template(config: $config);
$page->setTemplate(template_file: "admin/parts/index.tpl.php");
$page->set(name: "parts", value: $parts);
$inner = $page->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: "Parts");
$layout->set(name: "page_content", value: $inner);
$layout->echoToScreen();
