<div class="PagePanel">
    <h1>All Parts</h1>
    <ul>
        <?php foreach ($parts as $part): ?>
                    <li>
                        <strong><?= htmlspecialchars($part->name) ?></strong><br>
                        <?= nl2br(htmlspecialchars($part->description)) ?><br>
                        <em>Alias:</em> <?= htmlspecialchars($part->part_alias) ?>
                        <em>Alias:</em> <?= htmlspecialchars($part->name) ?>
                    </li>
        <?php endforeach; ?>
    </ul>
</div>
