<?php
declare(strict_types=1);
header('Content-Type: application/json');

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Physical\PartsOSSCalculator;

if (!isset($_GET['ssop_mm'])) {
    echo json_encode(['error' => 'Missing ssop_mm']);
    exit;
}

$ssop_mm = (float) $_GET['ssop_mm'];
$height = PartsOSSCalculator::getBestFitHeight($ssop_mm);
echo json_encode(['height_mm' => $height]);
