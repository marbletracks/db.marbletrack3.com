<?php
declare(strict_types=1);

// This was used once on 4 July 2025 and hopefully never needed again.

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$partsRepo = new \Database\PartsRepository($mla_database, 'en');
$allParts = $partsRepo->findAll();

foreach ($allParts as $part) {

    $mla_database->executeSQL(
        'UPDATE parts SET slug = ? WHERE part_id = ?',
        'si',
        [$part->slug, $part->part_id]
    );
    print_rob( "Updated part {$part->part_id} with slug: {$part->slug}\n",false);
}

echo "\nDone.\n";

