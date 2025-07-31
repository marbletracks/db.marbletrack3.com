<?php
declare(strict_types=1);

// File: /wwwroot/admin/parts/images/save_photos.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

$request = new RobRequest();

if (!$is_logged_in->isLoggedIn()) {
    $request->jsonError('Unauthorized', 401);
}

header('Content-Type: application/json');

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $request->jsonError('Invalid JSON input', 400);
}

// Extract data
$part_id = intval($input['part_id'] ?? 0);
$image_urls = $input['image_urls'] ?? [];
$worker_ids = $input['worker_ids'] ?? [];

// Validate input
if ($part_id <= 0) {
    $request->jsonError('Invalid part_id', 400);
}

if (empty($image_urls) || !is_array($image_urls)) {
    $request->jsonError('No image URLs provided', 400);
}

if (!is_array($worker_ids)) {
    $request->jsonError('Invalid worker_ids format', 400);
}

// Convert worker_ids to integers and filter out invalid ones
$worker_ids = array_filter(array_map('intval', $worker_ids), function($id) { return $id > 0; });

try {
    $mla_database->beginTransaction();

    // Initialize repositories
    $photoRepo = new \Database\PhotoRepository($mla_database);
    $partsRepo = new \Database\PartsRepository($mla_database, 'en');
    $workersRepo = new \Database\WorkersRepository($mla_database, 'en');

    // Verify part exists
    $part = $partsRepo->findById($part_id);
    if (!$part) {
        throw new Exception("Part not found");
    }

    $saved_photos = [];
    $photos_processed = 0;

    foreach ($image_urls as $url) {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            continue; // Skip invalid URLs
        }

        // Create or find photo record
        $photos = $photoRepo->findOrCreateByUrls([$url]);
        if (empty($photos)) {
            continue; // Skip if photo creation failed
        }

        $photo = $photos[0];
        $saved_photos[] = $photo;
        $photos_processed++;

        // Link photo to part
        $partsRepo->setPartId($part_id);
        $partsRepo->savePhotos([$photo]);

        // Link photo to selected workers
        foreach ($worker_ids as $worker_id) {
            $workersRepo->setWorkerId($worker_id);
            $workersRepo->savePhotos([$photo]);
        }
    }

    $mla_database->commit();

    $request->jsonSuccess([
        'message' => 'Photos saved successfully',
        'photos_processed' => $photos_processed,
        'workers_associated' => count($worker_ids),
        'part_name' => $part->name
    ]);

} catch (Exception $e) {
    $mla_database->rollBack();
    error_log('Error in save_photos.php: ' . $e->getMessage());
    $request->jsonError('Failed to save photos: ' . $e->getMessage(), 500);
}
