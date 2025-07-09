<div class="PagePanel">
    <h1>All Moments</h1>
    <ul>
        <?php foreach ($moments as $moment): ?>
            <li>
                <a href="/admin/moments/moment.php?id=<?= $moment->moment_id ?>">
                    edit ID <?= htmlspecialchars((string)$moment->moment_id) ?>:
                </a>
<?php if ($moment->photos[0]): ?>
                    <img src="<?= htmlspecialchars($moment->photos[0]->getUrl()) ?>" alt="Moment photo" style="max-width: 100px; max-height: 100px;"><br>
<?php endif; ?>
                <?= htmlspecialchars($moment->notes ?? '(no notes)') ?><br>
<?php if ($moment->phrase_id): ?>
                <em>Phrase ID:</em> <?= htmlspecialchars((string)$moment->phrase_id ?? '—') ?><br>
<?php endif; // ($moment->phrase_id): ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <p><a href="/admin/moments/moment.php">Create New Moment</a></p>
</div>
