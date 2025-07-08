<div class="PagePanel">
    <h1>All Moments</h1>
    <ul>
        <?php foreach ($moments as $moment): ?>
            <li>
                <strong>ID <?= htmlspecialchars((string)$moment->moment_id) ?>:</strong>
<?php if ($moment->photos[0]): ?>
                    <img src="<?= htmlspecialchars($moment->photos[0]->getUrl()) ?>" alt="Moment photo" style="max-width: 100px; max-height: 100px;"><br>
<?php endif; ?>
                <?= htmlspecialchars($moment->notes ?? '(no notes)') ?><br>
                <em>Phrase ID:</em> <?= htmlspecialchars((string)$moment->phrase_id ?? '—') ?><br>
                <a href="/admin/moments/moment.php?id=<?= $moment->moment_id ?>">Edit</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <p><a href="/admin/moments/moment.php">Create New Moment</a></p>
</div>
