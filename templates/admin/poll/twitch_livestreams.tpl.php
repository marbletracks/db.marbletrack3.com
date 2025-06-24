<div class="PagePanel">
    <h1>Twitch Livestream Poll Results</h1>
    <?php if (empty($results)): ?>
        <p>No livestreams found or saved.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($results as $res): ?>
                <li>
                    <?= htmlspecialchars($res['status']) ?>: <strong><?= htmlspecialchars($res['title']) ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p><a href="/admin/index.php">Return to Admin Home</a></p>
</div>
