<?php
declare(strict_types=1);

// File: /wwwroot/admin/ajax/update_moment_significance.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit;
}

header('Content-Type: application/json');

$moment_id = filter_input(INPUT_POST, 'moment_id', FILTER_VALIDATE_INT);
$perspective_id = filter_input(INPUT_POST, 'perspective_id', FILTER_VALIDATE_INT);
$perspective_type = $_POST['perspective_type'] ?? '';
$is_significant = isset($_POST['is_significant']) && $_POST['is_significant'] === 'true';

if (!$moment_id || !$perspective_id || !in_array($perspective_type, ['worker', 'part'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
    exit;
}

try {
    $moment_repo = new \Database\MomentRepository($mla_database);
    $moment_repo->updateSignificance($moment_id, $perspective_id, $perspective_type, $is_significant);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['status' => 'error', 'message' => 'An error occurred.', 'details' => $e->getMessage()]);
}
