<?php
// File: /templates/admin/workers/index.tpl.php
?>
<div class="PagePanel">
    <h1>All Workers</h1>

    <p><a href="/admin/workers/worker.php">Create New Worker</a></p>

    <ul style="list-style: none; padding: 0;">
        <?php foreach ($workers as $worker): ?>
            <li style="display: flex; align-items: flex-start; margin-bottom: 20px;">
                <?php if (!empty($worker->photos[0])): ?>
                    <a href="<?= htmlspecialchars($worker->photos[0]->getUrl()) ?>" style="margin-right: 15px;">
                        <img src="<?= htmlspecialchars($worker->photos[0]->getThumbnailUrl()) ?>" alt="Worker photo">
                    </a>
                <?php endif; ?>

                <div>
                    <strong><?= htmlspecialchars($worker->name) ?></strong><br>

                    <?php if (!empty($worker->description)): ?>
                        <?= nl2br(htmlspecialchars($worker->description)) ?><br>
                    <?php endif; ?>

                    <em>Alias:</em> <?= htmlspecialchars($worker->worker_alias ?? '—') ?><br>

                    <?php if (!empty($worker->created_at)): ?>
                        <em>Created:</em> <?= htmlspecialchars($worker->created_at) ?><br>
                    <?php endif; ?>

                    <a href="/admin/workers/worker.php?id=<?= htmlspecialchars($worker->worker_id) ?>">
                        Edit (id=<?= htmlspecialchars($worker->worker_id) ?>)
                    </a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <p><a href="/admin/workers/worker.php">Create New Worker</a></p>
</div>
