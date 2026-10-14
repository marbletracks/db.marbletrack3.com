<?php
declare(strict_types=1);

# Must include here because DH runs FastCGI https://www.phind.com/search?cache=zfj8o8igbqvaj8cm91wp1b7k
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

// Part photos are uploaded, straightened and cropped on badmint's /mt3/ page, which
// files them on b.robnugen.com and attaches them here through the API (mg #709).
// This address stays so old bookmarks and links still land somewhere useful.
if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$part_id = intval($_GET['part_id'] ?? 0);
if ($part_id <= 0) {
    header("Location: /admin/parts/images/");
    exit;
}

header("Location: https://badmint.robnugen.com/mt3/?part=" . $part_id);
exit;
