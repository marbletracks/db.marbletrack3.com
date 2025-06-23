<div class="PagePanel">
    <h1>All Notebooks</h1>
    <ul>
        <?php foreach ($notebooks as $notebook): ?>
            <li>
                <strong>ID <?= htmlspecialchars($notebook->notebook_id) ?>:</strong>
<?php if ($notebook->photos[0]): ?>
                    <img src="<?= htmlspecialchars($notebook->photos[0]->getUrl()) ?>" alt="Notebook photo" style="max-width: 100px; max-height: 100px;"><br>
<?php endif; ?>
                <?= htmlspecialchars($notebook->title ?? '(no title)') ?><br>
                <em>Created:</em> <?= htmlspecialchars($notebook->created_at ?? '—') ?><br>
                <a href="/admin/notebooks/notebook.php?id=<?= $notebook->notebook_id ?>">Edit</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <p><a href="/admin/notebooks/notebook.php">Create New Notebook</a></p>
</div>
