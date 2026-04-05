<div class="PagePanel">
    <h1><?= htmlspecialchars($marble->marble_name ?? '') ?></h1>

    <p>
        <strong>Size:</strong> <?= htmlspecialchars(ucfirst($marble->size)) ?><br>
        <strong>Color:</strong> <?= htmlspecialchars($marble->color) ?>
        <?php if ($marble->quantity > 1): ?>
            <br><strong>Quantity:</strong> <?= $marble->quantity ?>
        <?php endif; ?>
        <?php if ($marble->team_name): ?>
            <br><strong>Team:</strong> <?= htmlspecialchars($marble->team_name) ?>
        <?php endif; ?>
    </p>

    <?php if ($marble->description): ?>
        <h2>About</h2>
        <p><?= nl2br(htmlspecialchars($marble->description)) ?></p>
    <?php endif; ?>

    <?php if (!empty($marble->moments)): ?>
        <h2>History</h2>
        <div class="marble-moments">
            <ul>
                <?php foreach ($marble->moments as $moment): ?>
                    <li>
                        <?php if ($moment->moment_date): ?>
                            (<?= htmlspecialchars($moment->moment_date) ?>)
                        <?php endif; ?>
                        <?php if ($moment->take_id): ?>
                            <?= $moment->take_id ?>:<?= $moment->frame_start ?>-<?= $moment->frame_end ?>
                        <?php endif; ?>
                        <?= htmlspecialchars($moment->notes ?? '') ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
