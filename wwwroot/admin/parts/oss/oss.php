<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Database\PartsOSSStatusRepository;

$repo = new PartsOSSStatusRepository($mla_database);
$status_id = (int) ($_GET['id'] ?? 0);
$status = $status_id > 0 ? $repo->findById($status_id) : null;

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $parts_oss_status_id = isset($_POST['parts_oss_status_id']) ? (int)$_POST['parts_oss_status_id'] : null;

    $part_id = (int) ($_POST['part_id'] ?? 0);
    $ssop_label = trim($_POST['ssop_label'] ?? '');
    $ssop_mm = (float) ($_POST['ssop_mm'] ?? 0);
    $height_orig = (float) ($_POST['height_orig'] ?? 0);
    $height_best = (float) ($_POST['height_best'] ?? 0);
    $height_now = $_POST['height_now'] !== '' ? (float) $_POST['height_now'] : null;

    if ($part_id && $ssop_label && $ssop_mm) {
        $repo->insertOrUpdate(
            $parts_oss_status_id,
            $part_id,
            $ssop_label,
            $ssop_mm,
            $height_orig,
            $height_best,
            $height_now
        );
        header("Location: /admin/parts/oss/index.php");
        exit;
    } else {
        $errors[] = "All required fields must be filled.";
    }
}

$page = new Template(config: $config);
$page->setTemplate("admin/parts/oss/oss.tpl.php");
$page->set("status", $status);
// For prefilling when creating a new Spiral Support Outer Placement
$prefill_ssop_mm = $_GET['ssop_mm'] ?? "null";
$prefill_height_best = $_GET['height_best'] ?? "null";
$page->set("prefill_ssop_mm", $prefill_ssop_mm);
$page->set("prefill_height_best", $prefill_height_best);
$partsRepository = new \Database\PartsRepository($mla_database, "en");
$possParts = $partsRepository->findPossParts();
$page->set("possParts", $possParts);

$page->set("errors", $errors);
$inner = $page->grabTheGoods();

$layout = new Template(config: $config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", $status ? "Edit OSS Support" : "Create OSS Support");
$layout->set("page_content", $inner);
$layout->echoToScreen();
