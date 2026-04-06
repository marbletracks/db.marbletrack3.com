<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Marble Track 3 - The ultimate gravity-powered theme park for marbles"/>
    <title><?= $page_title ?? 'Marble Track 3' ?></title>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/menu.css">
</head>
<body>
    <div class="NavBar">
        <a href="/">Home</a> |
        <a href="/rides/">Rides</a> |
        <a href="/workers/">Our Crew</a> |
        <a href="/marbles/">Residents</a>
    </div>
    <div class="PageWrapper">
        <?= $page_content ?>
    </div>
    <footer class="SiteFooter">
        <a href="/parts/">Technical Archive</a> |
        <a href="/moments/">Construction History</a>
    </footer>
</body>
</html>