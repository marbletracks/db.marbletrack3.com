<div class="PagePanel">
    <?php if (!empty($username)): ?>
        Welcome back <?= $username ?>! <br />
    <?php else: ?>
        Welcome to MarbleTrack3! <br />
    <?php endif; ?>
</div>
<h1>Welcome to MarbleTrack3</h1>
<p>This is the MarbleTrack3 database and community site for marble track enthusiasts.</p>

<?php if (!empty($username)): ?>
    <div class="PagePanel">
        <h3>Your Account</h3>
        <p>You are logged in as <?= $username ?>.</p>
        <p><a href="/admin/">Go to Admin Dashboard</a></p>
        <p><a href="/logout/">Logout</a></p>
    </div>
<?php else: ?>
    <div class="PagePanel">
        <h3>Join the Community</h3>
        <p><a href="/login/">Login</a> to access more features and manage content.</p>
    </div>
<?php endif; ?>

<div class="PagePanel">
    <h3>Explore</h3>
    <p><a href="/workers/">Browse Workers</a> - See all the marble track workers</p>
</div>

<div class="fix">
    <p>Sentimental version: <?= $site_version ?></p>
</div>