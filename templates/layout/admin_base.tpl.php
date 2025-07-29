<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content=""/>
    <title><?= $page_title ?? 'MarbleTrack3 Admin' ?></title>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/menu.css">
</head>
<body>
    <div class="NavBar">
        <!-- dropdown -->
        <div class="dropdown">
            <a href="/admin/scripts/generate_static_site.php">Generate Site ▾</a>
            <div class="dropdown-menu">
                <a href="/">View Site</a>
            </div>
        </div> |
        <a href="/admin/">Dashboard</a> |
        <a href="/admin/photos">Photos</a> |
        <!-- dropdown -->
        <div class="dropdown">
            <a href="/admin/livestreams">Livestreams ▾</a>
            <div class="dropdown-menu">
                <a href="/admin/episodes">Episodes</a>
                <a href="/admin/poll/youtube_livestreams.php">Get YT Livestreams</a>
                <a href="/admin/poll/twitch_livestreams.php">Get Twitch Livestreams</a>
            </div>
        </div> |
        <a href="/admin/workers">Workers</a> |
        <div class="dropdown">
            <a href="/admin/parts">Parts ▾</a>
            <div class="dropdown-menu">
                <a href="/admin/parts/part.php" target="_blank">Create Part</a>
            </div>
        </div> |
        <div class="dropdown">
            <a href="/admin/moments">Moments ▾</a>
            <div class="dropdown-menu">
                <a href="/admin/moments/realtime.php">Realtime</a>
            </div>
        </div> |
        <a href="/admin/parts/oss">Outer Spiral Supports</a> |
        <!-- dropdown -->
        <div class="dropdown">
          <a href="/admin/notebooks">Notebooks ▾</a>
          <div class="dropdown-menu">
            <a href="/admin/notebooks/pages">Pages</a>
          </div>
        </div> |
    </div>
    <div class="PageWrapper">
        <?= $page_content ?>
    </div>
</body>
</html>
