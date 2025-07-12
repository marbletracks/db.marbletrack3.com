<div class="PagePanel">
    What's up <?= $username ?>? <br />
</div>
<h1>Welcome to the MarbleTrack3 Admin Dashboard</h1>
<p>This page can show numbers of workers, parts, snippets, etc</p>

<div class="PagePanel">
    <h2>System Status</h2>
    <p>Database Backups: <strong><?= $backup_count ?></strong></p>
    <p>Most Recent Backup: 
        <strong>
            <?php if ($latest_backup_time > 0): ?>
                <?php
                    $date = new DateTime('@' . $latest_backup_time);
                    $date->setTimezone(new DateTimeZone('Asia/Tokyo'));
                    echo $date->format('Y-m-d H:i:s T');
                ?>
            <?php else: ?>
                Never
            <?php endif; ?>
        </strong>
    </p>
</div>

<?php
if ($has_pending_migrations) {
        echo "<h3>Pending DB Migrations</h3>";
        echo "<a href='/admin/migrate_tables.php'>Click here to migrate tables</a>";
    }
?>

<div class="PagePanel">
    <a href="/logout/">Logout</a> <br />
</div>
<div class="fix">
    <p>Sentimental version: <?= $site_version ?></p>
</div>
