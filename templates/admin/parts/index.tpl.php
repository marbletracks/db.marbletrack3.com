<div class="PagePanel">
    <h1>All Parts</h1>
    <ul>
        <?php foreach ($parts as $part): ?>
                    <li>
                        <strong><?= htmlspecialchars($part->name) ?></strong><br>
                        <?= nl2br(htmlspecialchars($part->description)) ?><br>
                        <em>Alias:</em> <?= htmlspecialchars($part->part_alias) ?>
                        <a href="/admin/parts/part.php?id=<?= (int) $part->part_id ?>">Edit</a>
                    </li>
        <?php endforeach; ?>
    </ul>
</div>
