<div class="PagePanel">
    <h1>All Workers</h1>
    <ul>
        <?php foreach ($workers as $worker): ?>
            <li>
                <strong><?= htmlspecialchars($worker->name) ?></strong><br>
                <?= nl2br(htmlspecialchars($worker->description)) ?><br>
                <em>Alias:</em> <?= htmlspecialchars($worker->worker_alias) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
