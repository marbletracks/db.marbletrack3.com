<div class="PagePanel">
    <h1>All Episodes</h1>
    <p><a href="/admin/episodes/episode.php">Create New Episode</a></p>
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
                    <?php endif; ?><br>
                    <a href="/admin/episodes/episode.php?episode_id=<?= $episode->episode_id ?>">Edit</a>
                </li>
        <?php endforeach; ?>
    </ul>
</div>
