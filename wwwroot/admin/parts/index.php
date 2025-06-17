<?php
declare(strict_types=1);

# Must include here because DH runs FastCGI https://www.phind.com/search?cache=zfj8o8igbqvaj8cm91wp1b7k
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

// Redirect if not logged in
if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

// Instantiate repository
$repo = new \Database\PartsRepository($mla_database, 'en');

// Fetch parts
$parts = $repo->findAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parts List</title>
</head>
<body>
    <h1>All Parts</h1>
    <ul>
        <?php foreach ($parts as $part): ?>
                <li>
                    <strong><?= htmlspecialchars($part->name) ?></strong><br>
                    <?= nl2br(htmlspecialchars($part->description)) ?><br>
                    <em>Alias:</em> <?= htmlspecialchars($part->part_alias) ?>
                    <em>Alias:</em> <?= htmlspecialchars($part->name) ?>
                </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>