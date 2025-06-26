<div class="PagePanel">
    <h1>Livestreams polled from YouTube</h1>
    <ul>
        <?php foreach ($livestreams as $stream): ?>
                <li>
                    <strong><?= $stream->title ?></strong><br>
                    <?= nl2br(htmlspecialchars($stream->description)) ?><br>
                    <em>Status:</em> <?= htmlspecialchars($stream->status) ?><br>
                    <em>Published:</em> <?= htmlspecialchars($stream->published_at ?? '—') ?><br>
                    <em>Watch on</em> <a href="<?= htmlspecialchars($stream->watch_url) ?>" target="_blank"><?= $stream->platform ?></a><br>
                    <?php if ($stream->status === 'not'): ?>
                        &nbsp;
                        <a class="btn" href="/admin/episodes/episode.php?livestream_id=<?= $stream->livestream_id ?>">
                            🎥 Create Episode
                        </a>
                    <?php endif; ?>
                </li>
        <?php endforeach; ?>
    </ul>
</div>
