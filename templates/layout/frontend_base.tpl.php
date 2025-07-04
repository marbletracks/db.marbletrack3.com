<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MarbleTrack3 - Community site for marble track enthusiasts"/>
    <title><?= $page_title ?? 'MarbleTrack3' ?></title>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/menu.css">
</head>
<body>
    <div class="NavBar">
        <a href="/">Home</a> |
        <a href="/workers/">Workers</a> |
        <a href="/parts/">Parts</a>
    </div>
    <div class="PageWrapper">
        <?= $page_content ?>
    </div>
</body>
</html>