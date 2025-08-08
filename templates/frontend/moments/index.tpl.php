<h1>Moments in Marble Track 3</h1>

<div class="moment-grid">
    <?php foreach ($moments as $moment): ?>
        <div class="moment-card">
            <h2>
                <a href="/moments/<?= htmlspecialchars($moment->slug) ?>/">
                    <?php if ($moment->take_id && $moment->frame_start): ?>
                        Take <?= $moment->take_id ?>: Frame <?= $moment->frame_start ?>
                        <?php if ($moment->frame_end && $moment->frame_end != $moment->frame_start): ?>
                            - <?= $moment->frame_end ?>
                        <?php endif; ?>
                    <?php else: ?>
                        Moment #<?= $moment->moment_id ?>
                    <?php endif; ?>
                </a>
            </h2>
            <?php if (!empty($moment->photos) && $moment->photos[0]): ?>
                <a href="/moments/<?= htmlspecialchars($moment->slug) ?>/">
                    <img src="<?= $moment->photos[0]->getThumbnailUrl() ?>" alt="Moment photo" style="max-width: 100%; height: auto;">
                </a>
            <?php endif; ?>
            <?php if ($moment->notes): ?>
                <p><?= nl2br(htmlspecialchars($moment->notes)) ?></p>
            <?php endif; ?>
            <?php if ($moment->moment_date): ?>
                <p class="moment-date"><small>Date: <?= htmlspecialchars($moment->moment_date) ?></small></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .moment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
    }

    .moment-card {
        background: #f9f9f9;
        border: 1px solid #ddd;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 1px 1px 3px rgba(0,0,0,0.1);
    }

    .moment-date {
        color: #666;
        margin-top: 0.5rem;
    }
</style>