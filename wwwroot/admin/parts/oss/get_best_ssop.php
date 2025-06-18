<?php
declare(strict_types=1);
header('Content-Type: application/json');

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

use Physical\PartsOSSCalculator;

if (!isset($_GET['height_mm'])) {
    echo json_encode(['error' => 'Missing height_mm']);
    exit;
}

$height_mm = (float) $_GET['height_mm'];
$ssop = PartsOSSCalculator::getBestFitSSOP($height_mm);
echo json_encode(['ssop_mm' => $ssop]);
