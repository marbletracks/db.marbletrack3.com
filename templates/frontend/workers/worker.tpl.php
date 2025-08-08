<div class="PagePanel">
    <h1>Worker Details</h1>

    <p>
        <strong>Name:</strong><br>
        <?= htmlspecialchars($worker->name ?? '') ?>
    </p>

    <p>
        <strong>Alias:</strong><br>
        <?= htmlspecialchars($worker->worker_alias ?? '') ?>
    </p>

    <p>
        <strong>Description:</strong><br>
        <?= htmlspecialchars($worker->description ?? '') ?>
    </p>

    <?php if (!empty($worker->photos)): ?>
        <h2>Photos</h2>
        <div class="worker-photos">
            <?php foreach ($worker->photos as $photo): ?>
                <p>
                    <a href="<?= htmlspecialchars($photo->getUrl()) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($photo->getThumbnailUrl()) ?>" alt="Worker photo">
                    </a>
                </p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($worker->moments)): ?>
        <h2>History</h2>
        <div class="worker-moments">
            <ul>
                <?php foreach ($worker->moments as $moment): ?>
                    <li><?= $moment->take_id ?>:<?= $moment->frame_start ?? '?' ?>-<?= $moment->frame_end ?? '?' ?> <?= htmlspecialchars($moment->notes ?? '') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>