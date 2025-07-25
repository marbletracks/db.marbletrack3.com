<div class="PagePanel">
    <h1>Moment Details</h1>

    <p>
        <strong>Moment ID:</strong><br>
        <?= htmlspecialchars($moment->moment_id ?? '') ?>
    </p>

    <?php if ($moment->take_id): ?>
        <p>
            <strong>Take:</strong><br>
            <?= htmlspecialchars($moment->take_id) ?>
        </p>
    <?php endif; ?>

    <?php if ($moment->frame_start || $moment->frame_end): ?>
        <p>
            <strong>Frame Range:</strong><br>
            <?php if ($moment->frame_start): ?>
                <?= htmlspecialchars($moment->frame_start) ?>
                <?php if ($moment->frame_end && $moment->frame_end != $moment->frame_start): ?>
                    - <?= htmlspecialchars($moment->frame_end) ?>
                <?php endif; ?>
            <?php elseif ($moment->frame_end): ?>
                End: <?= htmlspecialchars($moment->frame_end) ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <?php if ($moment->notes): ?>
        <p>
            <strong>Notes:</strong><br>
            <?= nl2br(htmlspecialchars($moment->notes)) ?>
        </p>
    <?php endif; ?>

    <?php if ($moment->moment_date): ?>
        <p>
            <strong>Date:</strong><br>
            <?= htmlspecialchars($moment->moment_date) ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($moment->photos)): ?>
        <h2>Photos</h2>
        <div class="moment-photos">
            <?php foreach ($moment->photos as $photo): ?>
                <p>
                    <a href="<?= htmlspecialchars($photo->getUrl()) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($photo->getThumbnailUrl()) ?>" alt="Moment photo">
                    </a>
                </p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>