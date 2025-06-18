<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content=""/>
    <title><?= $page_title ?? 'MarbleTrack3 Admin' ?></title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <div class="NavBar">
        <a href="/">View Site</a> |
        <a href="/admin/">Dashboard</a> |
        <a href="/admin/livestreams">Livestreams</a> |
        <a href="/admin/episodes">Episodes</a> |
        <a href="/admin/workers">Workers</a> |
        <a href="/admin/parts">Parts</a> |
        <a href="/admin/parts/oss">Outer Spiral Supports</a> |
        <a href="/admin/youtube/poll_livestreams.php">Gimme Livestreams</a>
    </div>
    <div class="PageWrapper">
        <?= $page_content ?>
    </div>
</body>
</html>
