<div class="PagePanel">
    <h1>Part Details</h1>

    <p>
        <strong>Name:</strong><br>
        <?= htmlspecialchars($part->name ?? '') ?>
    </p>

    <p>
        <strong>Alias:</strong><br>
        <?= htmlspecialchars($part->part_alias ?? '') ?>
    </p>

    <p>
        <strong>Description:</strong><br>
        <?= htmlspecialchars($part->description ?? '') ?>
    </p>

    <?php if (!empty($part->photos)): ?>
        <h2>Photos</h2>
        <div class="part-photos">
            <?php foreach ($part->photos as $photo): ?>
                <p>
                    <a href="<?= htmlspecialchars($photo->getUrl()) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($photo->getThumbnailUrl()) ?>" alt="Part photo">
                    </a>
                </p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($part->moments)): ?>
        <h2>History</h2>
        <div class="part-moments">
            <ul>
                <?php foreach ($part->moments as $moment): ?>
                    <li><?= $moment->take_id ?>:<?= $moment->frame_start ?? '?' ?>-<?= $moment->frame_end ?? '?' ?><?= htmlspecialchars($moment->notes ?? '') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
