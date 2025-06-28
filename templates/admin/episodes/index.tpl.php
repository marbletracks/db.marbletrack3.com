<?php
// File: /templates/admin/episodes/index.tpl.php
?>
<div class="PagePanel">
    <h1>All Episodes</h1>

    <p><a href="/admin/episodes/episode.php">Create New Episode</a></p>

    <ul style="list-style: none; padding: 0;">
        <?php foreach ($episodes as $episode): ?>
            <li style="display: flex; align-items: flex-start; margin-bottom: 20px;">
                <?php if (!empty($episode->photos[0])): ?>
                    <a href="<?= htmlspecialchars($episode->photos[0]->getUrl()) ?>" style="margin-right: 15px;">
                        <img src="<?= htmlspecialchars($episode->photos[0]->getThumbnailUrl()) ?>" alt="Episode cover" style="max-width: 120px; height: auto;">
                    </a>
                <?php endif; ?>

                <div>
                    <strong><?= htmlspecialchars($episode->title) ?></strong><br>

                    <?php if (!empty($episode->episode_english_description)): ?>
                        <?= nl2br(htmlspecialchars($episode->episode_english_description)) ?><br>
                    <?php endif; ?>

                    <em>Created:</em> <?= htmlspecialchars($episode->created_at) ?><br>

                    <?php if (!empty($episode->livestream_id)): ?>
                        <em>Linked to livestream ID:</em> <?= htmlspecialchars($episode->livestream_id) ?><br>
                    <?php else: ?>
                        <em>No livestream linked</em><br>
                    <?php endif; ?>

                    <a href="/admin/episodes/episode.php?episode_id=<?= htmlspecialchars($episode->episode_id) ?>">
                        Edit (id=<?= htmlspecialchars($episode->episode_id) ?>)
                    </a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <p><a href="/admin/episodes/episode.php">Create New Episode</a></p>
</div>
