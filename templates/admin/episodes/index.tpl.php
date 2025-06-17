<div class="PagePanel">
    <h1>All Episodes</h1>
    <ul>
        <?php foreach ($episodes as $episode): ?>
                <li>
                    <strong><?= htmlspecialchars($episode->title) ?></strong><br>
                    <?= nl2br(htmlspecialchars($episode->episode_english_description)) ?><br>
                    <em>Created:</em> <?= htmlspecialchars($episode->created_at) ?><br>
                    <?php if ($episode->livestream_id): ?>
                            <em>Linked to livestream ID:</em> <?= (string) $episode->livestream_id ?>
                    <?php else: ?>
                            <em>No livestream linked</em>
                    <?php endif; ?>
                </li>
        <?php endforeach; ?>
    </ul>
</div>
