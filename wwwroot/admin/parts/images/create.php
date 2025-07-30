<?php
declare(strict_types=1);

# Must include here because DH runs FastCGI https://www.phind.com/search?cache=zfj8o8igbqvaj8cm91wp1b7k
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

// Redirect if not logged in
if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

// Get part_id from URL
$part_id = intval($_GET['part_id'] ?? 0);
if ($part_id <= 0) {
    header("Location: /admin/parts/images/");
    exit;
}

// Load the part
$partsRepo = new \Database\PartsRepository(
    db: $mla_database,
    langCode: 'en',
);

$part = $partsRepo->findById($part_id);
if (!$part) {
    header("Location: /admin/parts/images/");
    exit;
}

// Load all workers for selection
$workersRepo = new \Database\WorkersRepository($mla_database, 'en');
$workers = $workersRepo->findAll();

$page = new \Template(config: $config);
$page->setTemplate(template_file: "admin/parts/images/create.tpl.php");
$page->set(name: "part", value: $part);
$page->set(name: "workers", value: $workers);
$inner = $page->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: "Upload Images for " . $part->name);
$layout->set(name: "page_content", value: $inner);
$layout->echoToScreen();