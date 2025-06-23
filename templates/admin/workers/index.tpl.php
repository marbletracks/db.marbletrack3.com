<div class="PagePanel">
    <h1>All Workers</h1>
    <ul>
        <?php foreach ($workers as $worker): ?>
            <li>
                <strong><?= htmlspecialchars($worker->name) ?></strong><br>
                <?php if ($worker->photos[0]): ?>
                    <img src="<?= htmlspecialchars($worker->photos[0]->getThumbnailUrl()) ?>" alt="Worker photo">
                <?php endif; ?>
                <?= nl2br(htmlspecialchars($worker->description)) ?><br>
                <em>Alias:</em> <?= htmlspecialchars($worker->worker_alias) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
