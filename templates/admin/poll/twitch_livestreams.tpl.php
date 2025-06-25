<div class="PagePanel">
    <h1>Twitch Livestream Poll Results</h1>
    <?php if (empty($results)): ?>
        <p>No livestreams found or saved.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($results as $res): ?>
                <li>
                    <?= htmlspecialchars($res['status']) ?>: <strong><?= htmlspecialchars($res['title']) ?></strong>
                    <?php if (!empty($res['url'])): ?>
                        <a href="<?= htmlspecialchars($res['url']) ?>" target="_blank">Watch on Twitch</a><br>
                    <?php endif; ?>
                    <?php if (!empty($res['thumbnail_url'])): ?>
                        <img src="<?= htmlspecialchars($res['thumbnail_url']) ?>" alt="Thumbnail" style="max-height: 120px;"><br>
                    <?php endif; ?>
                    <?php if (!empty($res['duration'])): ?>
                        <em>Duration:</em> <?= htmlspecialchars($res['duration']) ?><br>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p><a href="/admin/index.php">Return to Admin Home</a></p>
</div>
