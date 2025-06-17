<div class="PagePanel">
    <h1>Livestreams polled from YouTube</h1>
    <ul>
        <?php foreach ($livestreams as $stream): ?>
                <li>
                    <strong><?= $stream->title ?></strong><br>
                    <?= nl2br(htmlspecialchars($stream->description)) ?><br>
                    <em>Status:</em> <?= htmlspecialchars($stream->status) ?><br>
                    <em>Published:</em> <?= htmlspecialchars($stream->published_at ?? '—') ?><br>
                    <em>YT Link:</em> <a href="https://www.youtube.com/watch?v=<?= htmlspecialchars($stream->youtube_video_id) ?>" target="_blank">Watch</a>
                </li>
        <?php endforeach; ?>
    </ul>
</div>
